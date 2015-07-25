# phpwiki-to-github
Script for migrating a phpwiki to github's wiki pages (markdown language)

This repository contains some scripts that will help you to migrate PHPWIKI to a GitHub Wiki.

The script connects directly to the database of PHP Wiki and extracts the source code of your wiki pages converting them to .md (markdown) files.

Basically, what you need to do is to copy the files inside "phpwikiScripts" folder into your phpwiki host on some folder named as you wish (for example: export).

Then you need to setup the index.php file (setting database host, user and password).

To check that you've done this step correctly, you can open this URL on your browser and check it displays your HomePage in markdown language:
```
http://www.your-wiki-host.com/export/?page=HomePage
```

Once you do that, you can use the scripts on the base folder to start converting your wiki.

To do that, follow this **instructions**:

1. Customize your parse.php file (set the $base variable to point to your phpwiki).

2. Choose the start page (page you'll first get... then the script will start fetching all related pages).

3. Run the beginParse script like this
```bash
./beginParse HomePage
```

4. If everything goes ok, you'll end up getting your pages. Sometimes, you'll get error messages saying that you may want to blacklist some page.
In this case, you can put in blacklist.txt all the pages you want to blacklist.
The script will provide some pages returning errors on newBlacklist.txt. You can just copy pages from newBlacklist.txt to blacklist.txt

5. Repeat step 3 until the script finished ok.

6. Run the "fix-links.php" script by executing
```bash
php fix-links.php
```

7. Now you'll have a folder named data with a bunch of .md files that you can upload to Github Wikis using some git clients, or any other method you like.

**NOTE:** Please note that this scripts are extremely beta, and you should use it with care. I made them to port some wiki pages (not as a nice-strong project).
If you consider you need to modify some parts, most probably you're right. Just do it :)

###Disclaimer

GitHub is a registered trademark of Github, Inc.
http://unibrander.com/united-states/1872043US/github.html

###License

The MIT License (MIT) - [LICENSE.md](LICENSE.md)

Copyright &copy; 2015 Federico Bricker

Author: Federico Bricker
