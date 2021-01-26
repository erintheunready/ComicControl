<?php

$ilang['firststep'] = "<p>Welcome to ComicControl!</p><p>Let's get started.</p><p>First, we need some database information.  ComicControl requires access to a MySQL database.  For information on creating a database for ComicControl, please visit the <a href='http://comicctrl.com/getting-started#database'>guide on the support website.</a></p>";
$ilang['dbbuilderror'] = "There was an error trying to connect to the database with the provided information.  Please try again.";
$ilang['secondstep'] = "<p>Great. Now that we have a database connection established, we can get started on creating your site.  We've filled as much as we can automatically, but we need a little more information to finish creating your new website.</p>";
$ilang['thirdstep'] = "<p>Awesome! Your site's halfway there.  The next thing you'll need is to create an administrative user to manage the site.</p>";
$ilang['fourthstep'] = "<p>Almost there! To finish your site, you'll need to create your first module. This module will be your homepage to start, but you can change this later in your site options.</p>";
$ilang['complete'] = "<p>Congratulations! You've finished installing ComicControl and you're ready to start managing your new comic website.  To log in, simply go to <a href='" . $ccurl . "'>" . $ccurl . "</a>. For more help on using ComicControl, check out the <a href='http://comicctrl.com/support'>ComicControl support page</a> for more info!</p>";

$ilang['next'] = "Next &gt;";
$ilang['error-prefix'] = "This prefix must be no more than 7 characters in length, made up of only letters, numbers, and underscores. It cannot be blank.";

$ilang['Database host'] = 'Database host';
$ilang['Database name'] = 'Database name';
$ilang['Database user'] = 'Database user';
$ilang['Database password'] = 'Database password';
$ilang['Prefix for tables'] = 'Prefix for tables';

$ilang['Site title'] = 'Site title';
$ilang['Site root'] = 'Site root';
$ilang['Relative path'] = 'Relative path';
$ilang['ComicControl path'] = 'ComicControl path';
$ilang['Administrator language'] = 'Administrator language';
$ilang['Time zone'] = 'Time zone';
$ilang['Date format'] = 'Date format';
$ilang['Time format'] = 'Time format';
$ilang['Disqus shortname'] = 'Disqus shortname';
$ilang['Site description'] = 'Site description';

$ilang['Username'] = 'Username';
$ilang['E-mail'] = 'E-mail';
$ilang['Password'] = 'Password';
$ilang['Confirm your password'] = 'Confirm your password';

$ilang['Module title'] = 'Module title';
$ilang['Module type'] = 'Module type';
$ilang['Display language'] = 'Display language';
$ilang['Page template'] = 'Page template';

$ilang['dbhost-tooltip'] = "This is the host for your database. It is commonly localhost or 127.0.0.1, but some shared hosting services have other addresses for their database hosts. Please check with your hosting provider for this information.";
$ilang['dbname-tooltip'] = "This is the name of your MySQL database.";
$ilang['dbuser-tooltip'] = "This is the username for the user that will be connecting to the MySQL database. Please make sure that this user has all permissions for your given database.";
$ilang['dbpassword-tooltip'] = "This is the password for the MySQL user that you have provided.";
$ilang['tableprefix-tooltip'] = "This is a prefix that will be added to your table names in order to make this a unique installation of ComicControl.  For example, if your comic is &quot;Example Comic&quot; and you choose the prefix &quot;ec_&quot;, your comics table will be named &quot;cc_ec_comics&quot;.  Please choose a prefix no more than 7 characters in length, made up of only letters, numbers, and underscores. The default setting for this is &quot;main_&quot;, but is recommended that you choose a unique prefix if you plan on having multiple installations of ComicControl on the same hosted space.";

$ilang['sitetitle-tooltip'] = "This is the title of your website.  Your site title must include at least one letter or number.";
$ilang['siteroot-tooltip'] = "This is the base URL that you are installing ComicControl at.  For example, if your website is testwebsite.com and you are installing ComicControl to the main folder of the site, this URL would be http://www.testwebsite.com/.  If you were hosting at the same site but in the mycomic/ folder, this URL would still be http://www.testwebsite.com/.  There must be a forward slash at the end of this variable and the URL must start with http:// or https://.<br /><br />This field has been automatically generated based on the URL of the installation file, so please only change this if the information is incorrect.";
$ilang['relativepath-tooltip'] = "This option will only be filled if you are not installing ComicControl in the root folder of your website.  For example, if you are installing ComicControl in http://www.testwebsite.com/mycomic/, this variable should be filled in as mycomic/.  There must be a forward slash at the end of this variable.<br /><br />This field has been automatically generated based on the URL of the installation file, so please only change this if the information is incorrect.";
$ilang['ccpath-tooltip'] = "This will only be changed if you changed the name of the main \"comiccontrol\" folder.  For example, if you changed \"comiccontrol\" to \"cc\", this variable would be \"cc/\". There must be a forward slash at the end of this variable.<br /><br />This field has been automatically generated based on the URL of the installation file, so please only change this if the information is incorrect.";
$ilang['language-tooltip'] = "This option determines what language the ComicControl administration area will be displayed in.  For additional languages, please visit the ComicControl website.";
$ilang['timezone-tooltip'] = 'This is the time zone that your site will run in.  Blog posts and comic posts will be published according to the time zone you set here.';
$ilang['dateformat-tooltip'] = 'This option determines the format that dates will be displayed in on comic and blog posts.';
$ilang['timeformat-tooltip'] = 'This option determines the format that times will be displayed in on comic and blog posts.';
$ilang['disqus-tooltip'] = 'This option determines the Disqus shortname that will be used for your comic and blog posts\' comment sections.  If your Disqus comment forum is at example-comic.disqus.com, for example, your Disqus shortname is example-comic.';
$ilang['description-tooltip'] = 'This option contains the default meta description for your site.  This meta description will be used on pages that do not have another specific meta description specified in their options.  It is recommended that this field be less than 160 characters, but this field can hold up to 256 characters.';

$ilang['username-tooltip'] = 'This is the username that this user will use to log in.  Please use only letters, numbers, and underscores.  Usernames must be at least 4 characters long and no more than 32 characters.';
$ilang['email-tooltip'] = 'This is the e-mail that will be associated with this user.  The user can use this e-mail address to reset their password if lost.';
$ilang['password-tooltip'] = 'This is the password that this user will use to log in.  Passwords must be at least 8 characters long and no more than 32 characters.  The password can contain letters, numbers, and these special characters: ^$*+?.()|{}[]!@#%&;:\'"';
$ilang['confirm-tooltip'] = 'Please retype the password here to confirm it.';

$ilang['moduletitle-tooltip'] = "This is the title that will be associated with this module.  It will be displayed in the tab or window title when the user navigates to this page.";
$ilang['moduletype-tooltip'] = 'This option determines the type of module that will be associated with this page.';
$ilang['displaylang-tooltip'] = "This option determines the ComicControl-specific language that the page will be displayed in to the user.";
$ilang['template-tooltip'] = "This is the template file that will be used to display this page.";


?>