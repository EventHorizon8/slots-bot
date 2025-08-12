# slots-bot
Private project to find slots for Schengen visas (educational needs).

Init new command in `commands` folder

Useful commands:
```shell
# List of all commands
php yii help
 
 # Create migration
php yii migrate/create create_news_table
 
# Apply migrations
php yii migrate
# Revert last migration
php yii migrate/down

```

Test commands to check telegram bot:
```shell

# Retrieves information about the bot.
php yii test-messenger/get-info -m=telegram
# Retrieves a list of users subscribed to the bot.
php yii test-messenger/get-users -m=telegram
# Sends a message to a predefined list of recipients.
# recipient - you can take any user ID from the list of users
php yii test-messenger/send-message -m=telegram {recipient}
```

Setup on production server:
```shell
# Set up webbhook for the bot
# Firstly you need to fill CURRENT_URL= in environment variables
# then run the command
php yii messenger-sender/set-up-config -m=telegram

# Daily cron job to check for new slots
php yii check-changes
# Weekly cron job to send reports
php yii messenger-sender/send-weekly-report-message -m=telegram

```

TODO:
1. We need to prepare webhook, which can update snapshot in DB to use this info for the future.
2. We need to setup cron job to save snapshot in DB every day.
3. We need to move db queries into somewhere in the system.
