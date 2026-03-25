# CyberWolf - Windows Task Scheduler setup
# Run this script ONCE as Administrator to register the cron job.
# After that the cron runs automatically every minute in the background.
#
# Usage (in an elevated PowerShell window):
#   cd C:\xampp\htdocs\cyberwolf
#   .\setup_scheduler.ps1

$taskName   = "CyberWolfCron"
$phpExe     = "C:\xampp\php\php.exe"
$indexPhp   = "C:\xampp\htdocs\cyberwolf\index.php"
$workingDir = "C:\xampp\htdocs\cyberwolf"
$logFile    = "C:\xampp\htdocs\cyberwolf\cron.log"

# Remove old task if it already exists
if (Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue) {
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
    Write-Host "Removed existing task '$taskName'." -ForegroundColor Yellow
}

# Action: run php index.php /cron >> cron.log
$action = New-ScheduledTaskAction `
    -Execute $phpExe `
    -Argument "$indexPhp /cron >> $logFile 2>&1" `
    -WorkingDirectory $workingDir

# Trigger: every 1 minute, forever
$trigger = New-ScheduledTaskTrigger -RepetitionInterval (New-TimeSpan -Minutes 1) -Once -At (Get-Date)

# Run whether the user is logged in or not, with highest privileges
$settings = New-ScheduledTaskSettingsSet `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 2) `
    -MultipleInstances IgnoreNew `
    -StartWhenAvailable

$principal = New-ScheduledTaskPrincipal `
    -UserId "SYSTEM" `
    -LogonType ServiceAccount `
    -RunLevel Highest

Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Description "CyberWolf background cron - runs every minute" | Out-Null

Write-Host ""
Write-Host "Task '$taskName' registered successfully." -ForegroundColor Green
Write-Host "The cron will now run every minute automatically."
Write-Host ""
Write-Host "Useful commands:"
Write-Host "  Start now  : Start-ScheduledTask -TaskName '$taskName'"
Write-Host "  Stop       : Stop-ScheduledTask  -TaskName '$taskName'"
Write-Host "  Remove     : Unregister-ScheduledTask -TaskName '$taskName'"
Write-Host "  View log   : Get-Content $logFile -Tail 50"
Write-Host ""

# Trigger one immediate run so we don't have to wait a minute
Write-Host "Running the cron once right now..." -ForegroundColor Cyan
Start-ScheduledTask -TaskName $taskName
Start-Sleep -Seconds 5
Write-Host "Done. Check cron.log for output."
