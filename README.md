# WMTqueries 

`daterange.php` pulls query data from WMT for the last 90 days by day, based on a query-matched site name. Assumes site has significant traffic per day in order to extract useful data for a single day 90 times. Query-match site name functions works best for WMT profiles with more than one site, or international sites.

`parser.php` concatenates the data from the 90 .csv files created by `daterange.php`, grouped by site matches to aid Excel analysis.
