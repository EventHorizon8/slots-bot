# slots-bot
Private project to find slots for Schengen visas (educational needs).

Init new command in `commands` folder

Useful commands:
```shell
# List of all commands
 php yii help
 
 # Create migration
 yii migrate/create create_news_table
 
# Apply migrations
yii migrate

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
