# Info
Simple PHP MVC CMS with multi-brand/template and shopping cart support
  
# License
See LICENSE... spoiler alert: MIT (open-source... FREE!)
  
# Installation
1. Copy **config/customConfig.sample.php** to **config/customConfig.php**
1. Edit **config/customConfig.php**
  1. Set the **ERROR_EMAIL** to an email address that will receive any site errors
  1. Set the default server time zone to use if other than America/New_York
1. Copy **config/dbConfig.sample.php** to **config/dbConfig.php**
1. Edit **config/dbConfig.php**
  1. Set the database server variables appropriately (the database needs to exist in order for the wizard to do the rest)
  1. The Master.sql script needs to be ran to setup the database before the wizard can complete
1. Visit the site to start the Install Wizard
  
# URL Alias Rewrite Mapping Configuration
This Apache RewriteMap configuration change is required in order for shortcuts like /owners or /locations to work. Otherwise you would have to access them via /admin/home or /stores/readall.  
The rewrite mappings are stored in the **url-alias-map.txt** file.  
More info: http://httpd.apache.org/docs/2.2/mod/mod_rewrite.html#rewritemap  
1. Edit the Virtual Host section for the site and add the lines below - be sure to specify the path to the file according to your installation path.
 >         # RewriteMap
 >         RewriteEngine On
 >         RewriteMap    url-aliases        txt:/home/Projects/GPFC/url-alias-map.txt
 > 
 >         # Match domain specific aliases: domain.com/about /pages/view/12
 >         RewriteCond   ${url-aliases:%{SERVER_NAME}$1}  >"" [NC]
 >         RewriteRule   ^(.*)              ${url-aliases:%{SERVER_NAME}$1|/404.html} [NC,L,QSA]
 > 
 >         # Match generic aliases /example /admin/home
 >         RewriteCond   ${url-aliases:$1}  >"" [NC]
 >         RewriteRule   ^(.*)              ${url-aliases:$1|/404.html} [NC,L,QSA]
  
# Example Apache VirtualHost Configuration
 >		<VirtualHost *:80>
 > 		# Main domain
 >         	ServerName gpfc.com
 > 
 > 		# List all brands domains and aliases here
 >         	ServerAlias www.gpfc.com gpfc.local www.gpfc.local hutno8.com www.hutno8.com hutno8.local www.hutno8.local vinonbrew.com www.vinonbrew.com vinonbrew.local www.vinonbrew.local
 > 
 >         	ServerAdmin webmaster@gpfc.com
 > 
 > 		# Path to site folders
 >         	DocumentRoot /home/dirt/Projects/GPFC/public
 >         	<Directory /home/dirt/Projects/GPFC>
 >              	Options All
 >                 	AllowOverride All
 >                 	Require all granted
 >         	</Directory>
 >         	ErrorLog /home/dirt/Projects/GPFC/logs/error.log
 >         	CustomLog /home/dirt/Projects/GPFC/logs/access.log combined
 > 
 >         # RewriteMap
 >         RewriteEngine On
 >         RewriteMap    url-aliases        txt:/home/Projects/GPFC/url-alias-map.txt
 > 
 >         # Match domain specific aliases: domain.com/about /pages/view/12
 >         RewriteCond   ${url-aliases:%{SERVER_NAME}$1}  >"" [NC]
 >         RewriteRule   ^(.*)              ${url-aliases:%{SERVER_NAME}$1|/404.html} [NC,L,QSA]
 > 
 >         # Match generic aliases /example /admin/home
 >         RewriteCond   ${url-aliases:$1}  >"" [NC]
 >         RewriteRule   ^(.*)              ${url-aliases:$1|/404.html} [NC,L,QSA]
 >	</VirtualHost>
  
# Contact
@author Brock Hensley <brock.hensley@gmail.com>
