# FOR USE ON LIVE SERVER

# This script should be copied to /var/wfo-list and run from there
# It will be used when it, and the rest of the code, doesn't exist.
# it also needs to be in the same place as the other sync_from and sync_to scripts

# As copying of the bulk database is an occassional task - it isn't backed up.
# this script does it all, both exporting the database and moving it over to the staging server.

filename="/var/wfo-list/kew_sync/data/kew.sql"
mkdir -p /var/wfo-list/kew_sync/data/

echo "Backing up Kew db"
start=$(date +"%H:%M:%S")
echo "This may take a while. Starting at $start"
rm "{$filename}.gz"
mysqldump -u root kew > $filename
gzip $filename

rsync -Pav -e "ssh -i ~/.ssh/wfo-aws-03.pem" --delete {$filename}.gz wfo@wfo-staging.rbge.info:/var/wfo-list/kew_sync/data/kew.sql.gz