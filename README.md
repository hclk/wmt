# WMTqueries 

`daterange.php` pulls query data from WMT for the last 90 days by day, based on a query-matched site name. Assumes site has significant traffic per day in order to extract useful data for a single day 90 times. Query-match site name functions works best for WMT profiles with more than one site, or international sites.

CSV files generated are then concatenated automatically into single files by sitename, so `example.com`, `example.co.uk`, `example.co.nz` all have separate concatenated CSV files.
