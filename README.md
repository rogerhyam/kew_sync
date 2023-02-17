# kew_sync
A little utility to keep a local copy of IPNI and WCVP for sync jobs

scripts/daily_sync.php is run daily to completely replace the IPNI database with the latest version

scripts/wcvp_sync.php can be run on demand (about every 3 months) to copy over the WCVP/POWO dataset.
It should be followed by running scripts/wcvp_name_update.php which copies over the WFO IDs from the last dataset
and tries to match any blank names.


