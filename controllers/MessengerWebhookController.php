<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AvailableSlot;
use app\models\UserNotification;
use app\models\UserSubscriber;
use app\services\MessengerClient\MessengerClientInterface;
use app\services\MessengerClient\MessengerFactory;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class MessengerWebhookController extends Controller
{
    private MessengerClientInterface $bot;

    public $enableCsrfValidation = false; // Important for webhook


    public function actionIndex(string $messenger = ''): Response
    {
        $this->bot = MessengerFactory::create($messenger);

        $data = json_decode(Yii::$app->request->getRawBody(), true);

        if (!$data) {
            return $this->asJson(['status' => 'error', 'message' => 'Invalid JSON']);
        }

        try {
            if (isset($data['message'])) {
                $this->handleMessage($data['message']);
            } elseif (isset($data['callback_query'])) {
                $this->handleCallbackQuery($data['callback_query']);
            }

            return $this->asJson(['status' => 'ok']);
        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 500;
            return $this->asJson(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    private function handleMessage($message)
    {
        $chatId = (string) $message['chat']['id'];
        $text = $message['text'] ?? '';
        $user = $this->getOrCreateUser($message['chat']);

        $type = $message['data'] ?? '';

        switch ($text) {
            case '/start':
                if ($user->isAdmin()) {
                    $this->bot->sendMessage($chatId, "Welcome Admin! You can manage embassy slots and notify users.");
                } else {
                    $this->bot->sendMessage($chatId, "Welcome! You will receive notifications about available embassy slots.");
                }
                break;

            default:
                if ($user->isAdmin()) {
                    $this->handleAdminMessage($chatId, $text, $user, $type);
                } else {
                    $this->handleUserMessage($chatId, $text, $user);
                }
                break;
        }
    }

    private function handleAdminMessage($chatId, $text, $user, $type)
    {
        Yii::error($user->current_state);
        if ($user->current_state === 'waiting_for_datetime') {
            $this->handleDateTimeText($chatId, $text, $user);
        }

    }

    private function handleUserMessage($chatId, $text, $user)
    {
        $this->bot->sendMessage($chatId, "You will receive notifications about available embassy slots. Use /start to restart.");
    }

    private function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data'];
        $callbackQueryId = $callbackQuery['id'];

        $this->bot->answerCallbackQuery($callbackQueryId);

        $user = UserSubscriber::findOne(['chat_id' => $chatId]);
        if (!$user) return;

        if ($user->isAdmin()) {
            $this->handleAdminCallback($chatId, $messageId, $data, $user);
        } else {
            $this->handleUserCallback($chatId, $messageId, $data, $user);
        }
    }

    private function handleAdminCallback($chatId, $messageId, $data, $user)
    {
        Yii::error($data, $chatId);
        switch ($data) {
            case 'mark_available':
                $this->askForDateTime($chatId, $messageId, $user);
                break;

            case 'notify_users_yes':
                $this->notifyAllUsers($chatId, $messageId, $user);
                break;

            case 'notify_users_no':
                $this->bot->sendMessage($chatId, "Ok. Slot saved without notifying users.");
                break;

            default:
                if (strpos($data, 'date_') === 0) {
                    $selectedDate = substr($data, 5);
                    $this->showTimePicker($chatId, $messageId, $selectedDate, $user);
                } elseif (strpos($data, 'time_') === 0) {
                    $dateTime = substr($data, 5);
                    $this->handleAdminTimeSelection($chatId, $messageId, $dateTime, $user);
                }
                break;
        }
    }

    private function handleUserCallback($chatId, $messageId, $data, $user)
    {
        if (strpos($data, 'notify_') === 0) {
            $parts = explode('_', $data);
            $notificationType = $parts[1] . '_' . $parts[2]; // day_before or week_before
            $slotId = $parts[3] ?? null;

            if ($slotId && in_array($notificationType, [UserNotification::TYPE_DAY_BEFORE, UserNotification::TYPE_WEEK_BEFORE])) {
                $this->scheduleUserNotification($chatId, $messageId, $slotId, $notificationType);
            }
        } elseif ($data === 'cancel_notification') {
            $this->bot->sendMessage($chatId, "Ok. You won't receive notifications for this slot.");
        }
    }

    private function getOrCreateUser($chat)
    {
        $user = UserSubscriber::findOne(['chat_id' => $chat['id']]);

        if (!$user) {
            $user = new UserSubscriber();
            $user->chat_id = (string)$chat['id'];
            $user->username = $chat['username'] ?? null;
            $user->full_name = trim(($chat['first_name'] ?? '') . ' ' . ($chat['last_name'] ?? ''));
            $user->created_at = date('Y-m-d H:i:s');
            $user->updated_at = date('Y-m-d H:i:s');
            $user->is_admin = ($user->chat_id === Yii::$app->params['telegramAdminId']) ? 1 : 0;
            $user->save();
        }

        return $user;
    }


    private function askForDateTime($chatId, $messageId, $user)
    {
        $text = "Thx! Saved. Please, save the date and time where the slots will be available.\n\nType manually (YYYY-MM-DD HH:MM):";

        $this->bot->sendMessage($chatId, $text);
        $user->setState('waiting_for_datetime');
    }

    private function handleDateTimeText($chatId, $text, $user)
    {
        Yii::error($text);
        // Parse manual date/time input
        try {
            $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $text);
        } catch (\Throwable $e) {
            Yii::error("Date parsing error: " . $e->getMessage());
            $dateTime = null;
        }

        Yii::error($dateTime);
        if (!$dateTime) {
            Yii::error($dateTime);
            $this->bot->sendMessage($chatId, "Invalid format. Please use YYYY-MM-DD HH:MM (e.g., 2025-08-15 14:30)");
            return;
        }

        $this->saveSlotAndAskNotification($chatId, $dateTime->format('Y-m-d H:i:s'), $user);
    }

    private function showTimePicker($chatId, $messageId, $selectedDate, $user)
    {
        $text = "You selected: $selectedDate\nNow choose the time:";
        $keyboard = $this->generateTimePicker($selectedDate);

        $this->bot->sendMessage($chatId, $text, $keyboard);
        $user->setState('selecting_time', ['date' => $selectedDate]);
    }

    private function handleAdminTimeSelection($chatId, $messageId, $dateTime, $user)
    {
        list($date, $time) = explode('_', $dateTime, 2);
        $fullDateTime = "$date $time:00";

        $this->saveSlotAndAskNotification($chatId, $fullDateTime, $user);
    }

    private function saveSlotAndAskNotification($chatId, $dateTime, $user)
    {
        // Save the slot
        $stateData = $user->getStateData();
        $link = $stateData['link'] ?? '';

        $slot = new AvailableSlot();
        $slot->admin_chat_id = $chatId;
        $slot->slot_datetime = $dateTime;
        $slot->link = $link;
        $slot->notified_users = 0;
        $slot->created_at = date('Y-m-d H:i:s');
        $slot->updated_at = date('Y-m-d H:i:s');
        $slot->save();

        // Ask if admin wants to notify users
        $formattedDateTime = date('M d, Y \a\t H:i', strtotime($dateTime));
        $text = "Saved! Slot available: $formattedDateTime\n\nDo you want me to notify other users?";

        $keyboard = [
            ['text' => 'Yes', 'callback_data' => 'notify_users_yes'],
            ['text' => 'No', 'callback_data' => 'notify_users_no']
        ];

        Yii::error('Slot saved: ' . $slot->id . ' at ' . $formattedDateTime);

        $this->bot->sendMessage($chatId, $text, $keyboard);
        $user->setState('asking_user_notification', ['slot_id' => $slot->id]);
    }

    private function notifyAllUsers($chatId, $messageId, $user)
    {
        $stateData = $user->getStateData();
        $slotId = $stateData['slot_id'] ?? null;

        if (!$slotId) {
            $this->bot->sendMessage($chatId, "Error: Slot not found.");
            return;
        }

        $slot = AvailableSlot::findOne($slotId);
        if (!$slot) {
            $this->bot->sendMessage($chatId,"Error: Slot not found.");
            return;
        }

        $users = UserSubscriber::getAllUsers();
        $notifiedCount = 0;

        $formattedDateTime = date('M d, Y \a\t H:i', strtotime($slot->slot_datetime));

        foreach ($users as $targetUser) {
            $text = "🎯 New embassy slot available!\n\n";
            $text .= "📅 Date & Time: $formattedDateTime\n";
            if ($slot->link) {
                $text .= "🔗 Link: {$slot->link}\n";
            }
            $text .= "\nChoose your notification preference:";

            $keyboard = [
                ['text' => 'Notify me the day before', 'callback_data' => "notify_day_before_{$slotId}"],
                ['text' => 'Notify me the week before', 'callback_data' => "notify_week_before_{$slotId}"]
            ];

            if ($this->bot->sendMessage($targetUser->chat_id, $text, $keyboard)) {
                $notifiedCount++;
            }
        }

        // Update slot with notification count
        $slot->notified_users = $notifiedCount;
        $slot->save();

        $this->bot->sendMessage($chatId,  "✅ Notified $notifiedCount users about the available slot!");
    }

    private function scheduleUserNotification($chatId, $messageId, $slotId, $notificationType)
    {
        $slot = AvailableSlot::findOne($slotId);
        if (!$slot) {
            $this->bot->sendMessage($chatId,  "Error: Slot not found.");
            return;
        }

        // Calculate notification datetime
        $slotDateTime = new \DateTime($slot->slot_datetime);
        $notificationDateTime = clone $slotDateTime;

        if ($notificationType === UserNotification::TYPE_DAY_BEFORE) {
            $notificationDateTime->sub(new \DateInterval('P1D'));
            $period = 'day';
        } else {
            $notificationDateTime->sub(new \DateInterval('P7D'));
            $period = 'week';
        }

        // Save notification
        $notification = new UserNotification();
        $notification->user_chat_id = $chatId;
        $notification->slot_id = $slotId;
        $notification->notification_type = $notificationType;
        $notification->notification_datetime = $notificationDateTime->format('Y-m-d H:i:s');
        $notification->sent = 0;
        $notification->save();

        $formattedDate = $notificationDateTime->format('M d, Y \a\t H:i');
        $this->bot->sendMessage($chatId,  "Thank you! I'll notify you in $formattedDate (1 $period before the slot).");
    }

    private function generateTimePicker($date)
    {
        $times = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];
        $buttons = [];

        foreach ($times as $time) {
            $buttons[] = [
                'text' => $time,
                'callback_data' => 'time_' . $date . '_' . $time
            ];
        }

        $rows = array_chunk($buttons, 4);
        return $rows;
    }

    public function actionSendNotifications()
    {
        $notifications = UserNotification::find()
            ->where(['sent' => 0])
            ->andWhere(['<=', 'notification_datetime', date('Y-m-d H:i:s')])
            ->with('slot')
            ->all();

        foreach ($notifications as $notification) {
            $slot = $notification->slot;
            if (!$slot) continue;

            $slotDateTime = date('M d, Y \a\t H:i', strtotime($slot->slot_datetime));
            $message = "🔔 Reminder: Embassy slot available!\n\n";
            $message .= "📅 Date & Time: $slotDateTime\n";

            if ($slot->link) {
                $message .= "🔗 Link: {$slot->link}\n";
            }

            $message .= "\nDon't forget to check and book your slot!";

            $this->bot->sendMessage($notification->user_chat_id, $message);

            $notification->sent = 1;
            $notification->save();
        }

        echo "Sent " . count($notifications) . " notifications\n";
    }
}
