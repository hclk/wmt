# WMTqueries 

`daterange.php` pulls query data from WMT for the last 90 days by day, based on a query-matched site name. Assumes site has significant traffic per day in order to extract useful data for a single day 90 times. Query-match site name functions works best for WMT profiles with more than one site, or international sites.

CSV files generated are then concatenated automatically into single files by sitename, so `example.com`, `example.co.uk`, `example.co.nz` all have separate concatenated CSV files.

`ctr.php` can then be run to parse through a named folder looking for "concat" files to add to the analysis. Analysis is based on "branded" and "unbranded" queries, and the number of words in the query. It will output a CSV file with unique keys for each variation of type & number of words ready for analysis. Keywords are categorised fairly crudely by examining if any word in the query appears within the domain name. 

The script requires that the Google account allows "unsecure apps" to access data. Do this by navigating over your account name in the top right of any Webmaster Tools screen, click "Account" then enable the "Allow unsecure app access" option in the menu.

Intructions:
* Edit `daterange.php` opening variables to contain your email and password for the relevant WMT account
* Edit variable `$sitestring` to contain the string you wish to include analysis for: `All` will go through all attached sites
 * Example: `$sitestring = "topmugs";` will return all sites which contain "topmugs"
* Edit variable `$filter` to filter results by platform
 * Example: `$filter = "WEB";` will return only web results
 * Valid strings are: `ALL`, `WEB`, `MOBILE`, `IMAGE`, `VIDEO`

## Update 27-02-15

* Added option to enter "All" as sitestring
 * If the `$sitestring` variable is set to `All` then the script will loop through all sites attached to the account
 * For each site the script will parse through csvs to create a concatenated ranking file by day
* Fixed parser to allow folder-structure based WMT
 * For example WMT accounts which are divided by region via `/en-gb/`, `/en-ie/` etc it will correctly detect folder and parse throught he correct files - `GWTdata.php` modified to allow this functionality
* Fixed first line of concatenated output to correctly show the folder plus site ouput
* Fixed some console output issues
* Tweaked sleep rate to prevent being frozen out of WMT api