#!/bin/bash
#php makerealm.htmls.php
rm public/realmpop.min.js public/realmcharts.min.js
cat realmpop.js | php minit.php > public/realmpop.min.js
cat realmcharts.js | php minit.php > public/realmcharts.min.js
cat realmpop.cloud.js | php minit.php > public/cloud.min.js

