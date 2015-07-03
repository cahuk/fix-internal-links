
This program is in your articles inoperative (broken) internal links. 
The system finds its anchor and gets drawn in Google. Then overwrite the old links in the article to the new.

System writes the log (./log/) in every article with the id which starts if fails, then when you restart the system starts from the material on which it stopped/

If after we got to the page with the CAPTCHA, then the system does a delay of 2 hours. If we again find ourselves on such a page, the system again uvelichiet delay time according to the interval

The program was tested under Wordpress, but can work with other CMS or applications with a special setting

To start for wordpress:
	1. Add in file ./fix-internal-links.php constant HOST = 'yourdomain.com' the domains, to which you are relinking;
	2. Add your email in array $_report_email = array( 'yours@email1', 'yours@email2' )	
	3. If you plan to re-start the system from the beginning, then you need constant DELETE_LOG_AFTER_COMPLETE set to true, but log file will be deleted
	4. Specify access to a database file ./class/Db.php ($dbserver = 'localhost'; $dbuser = 'user'; $dbpassword = 'password'; $dbname = 'db_name';) to work with articles;
	5. execute a query to create a table with the results already obtained from the file links_query_cache
	6. Recommend run this program through ssh because the data in the browser script can take a long time. But if you believe that the site is not a lot of material, or a little broken links, and then you can run through a browser	www.yourdomain.com/fix-internal-links.php
