# Cron Job Setup for Recurring Tasks

This guide will help you set up automatic recurring task generation for your WebAwesomeTodos application.

## Step 1: Configure the Secret Key

1. **Edit `api.php`** and change the secret key:
   ```php
   // Around line 250, change this value to something secure
   $expected_key = 'your_secure_secret_key_here';
   ```

2. **Edit `cron_generate_recurring_tasks.php`** and use the same key:
   ```php
   // Around line 7, use the same secure key
   $secret_key = 'your_secure_secret_key_here';
   ```

## Step 2: Update the API URL

In `cron_generate_recurring_tasks.php`, update the `$api_url` to match your actual domain:
```php
$api_url = 'https://yourdomain.com/WebAwesomeTodos/api.php';
```

## Step 3: Test the Script Manually

Run the cron script manually to test:
```bash
php /path/to/your/project/cron_generate_recurring_tasks.php
```

Check the log file `recurring_tasks.log` in your project directory to see the results.

## Step 4: Set Up the Cron Job

### Option A: Using cPanel (if available)

1. Log into cPanel
2. Go to "Cron Jobs"
3. Add a new cron job with these settings:
   - **Minute**: `0` (at the top of the hour)
   - **Hour**: `*` (every hour)
   - **Day**: `*` (every day)
   - **Month**: `*` (every month)
   - **Weekday**: `*` (every day of the week)
   - **Command**: `php /full/path/to/your/project/cron_generate_recurring_tasks.php`

### Option B: Using Command Line (Linux/Mac)

1. Open crontab editor:
   ```bash
   crontab -e
   ```

2. Add this line (run every hour at minute 0):
   ```bash
   0 * * * * /usr/bin/php /full/path/to/your/project/cron_generate_recurring_tasks.php
   ```

### Option C: Using Windows Task Scheduler

1. Open Task Scheduler
2. Create a new task
3. Set trigger to run daily or hourly
4. Set action to run a program:
   - **Program**: `C:\path\to\php.exe`
   - **Arguments**: `C:\full\path\to\your\project\cron_generate_recurring_tasks.php`
   - **Start in**: `C:\full\path\to\your\project\`

## Step 5: Configure the Schedule

### Recommended Schedules:

**Hourly** (recommended for testing):
```bash
0 * * * * php /path/to/cron_generate_recurring_tasks.php
```

**Daily at midnight** (production):
```bash
0 0 * * * php /path/to/cron_generate_recurring_tasks.php
```

**Daily at 9 AM** (when users start their day):
```bash
0 9 * * * php /path/to/cron_generate_recurring_tasks.php
```

## Step 6: Monitor the Logs

Check `recurring_tasks.log` regularly to ensure the cron job is working:

```bash
tail -f /path/to/your/project/recurring_tasks.log
```

Sample log output:
```
[2025-11-27 10:00:01] Starting recurring task generation...
[2025-11-27 10:00:02] SUCCESS: Recurring tasks generated successfully.
[2025-11-27 10:00:02] Timestamp: 2025-11-27 10:00:02
[2025-11-27 10:00:02] Recurring task generation completed.
[2025-11-27 10:00:02] ----------------------------------------
```

## Troubleshooting

### Common Issues:

1. **Permission Denied**: Ensure PHP has write permissions for the log file
2. **API Timeout**: Increase the timeout in `cron_generate_recurring_tasks.php`
3. **Invalid Secret Key**: Make sure the keys match in both files
4. **Wrong API URL**: Verify the URL is accessible from your server

### Test the API Endpoint Directly:

```bash
curl "https://yourdomain.com/WebAwesomeTodos/api.php?action=generate_recurring_tasks&secret_key=your_secure_secret_key_here"
```

### Manual Testing:

You can also test the recurring task generation directly from your browser:
```
https://yourdomain.com/WebAwesomeTodos/api.php?action=generate_recurring_tasks&secret_key=your_secure_secret_key_here
```

## Security Notes:

- Change the default secret key to something secure
- Consider additional IP restrictions if needed
- Monitor the log files for any unauthorized access attempts
- Keep the log file in a secure location or configure log rotation

## Maintenance:

- Review the log file weekly
- Check that recurring tasks are being created as expected
- Adjust the cron schedule based on your usage patterns
- Consider implementing email notifications for failed runs