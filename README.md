# oc-14-to-23-script
A script to retrieve Orders, Customers and Products (including Options) from an OpenCart in version 1.4 and convert them into a format importable in OpenCart 2.3

First you need to modify the following variables with the information from the 1.4 database:
$servername
$username
$password
$dbname

If the 1.4 or 2.3 database has a prefix, modify the following variables (or leave blank if no prefix):
$prefix14
$prefix23

This script does not take into account if you have different "Customer Groups", the default language will be "French" (or the language with the ID 2) and the will use th default store ID (which is 0).
