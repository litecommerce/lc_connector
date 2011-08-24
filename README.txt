LiteCommerce Connector
=======================

1. What this module does
-------------------------

This module integrates your Drupal-based website with the LiteCommerce 3
shopping cart software as follows:

- Catalog and checkout sections are displayed within pages rendered by Drupal
- E-commerce blocks can be moved around the page like other "regular" Drupal
  blocks
- You can style the e-commerce elements with the CSS files in your Drupal theme
- Once user registers with your Drupal website, he/she goes to the checkout as
  a registered customer (user accounts are synchronized between the systems)


2. What is LiteCommerce
------------------------

LiteCommerce 3 is a free e-commerce solution, distributed under the terms of
the Open Source License (OSL 3.0). It can run as a stand-alone e-commerce
software or in connection with Drupal.

LiteCommerce 3 has a clean and smooth user interface with cool Ajax
enhancements like one-page checkout, quick product view and dragging products
to cart with the mouse.

You can find more information about LiteCommerce 3 at http://www.litecommerce.com


3. Installation
----------------

1) Download the module package. If you are reading this, it is likely you have
already done that :)

2) Unpack it to "sites/all/modules" (or "sites/default/modules", or
"sites/example.com/modules") subdirectory of your Drupal installation.

3) If you haven't installed the LiteCommerce 3 software yet, install it.

Download the stand-alone version of LiteCommerce 3 and install it on the same
server, where your Drupal-based website is located. You can install it into the
"litecommerce" directory beneath the "lc_connector" module directory, or into
any other directory, accessible from the Web.

What you should know when installing LiteCommerce 3:
- Both Drupal and LiteCommerce 3 should use the same MySQL database.
- Both LiteCommerce administrator and Drupal root administrator should have the
  same e-mail address.
- When LiteCommerce 3 is installed you should install and enable DrupalConnector
  module. You can do it on the "Module Marketplace" page under the "Add-ons" tab
  in the LiteCommerce back end.

You can find the detailed instructions on installing LiteCommerce 3 into
Drupal-based websites at
https://github.com/litecommerce/core/wiki/Installing-LiteCommerce

4) You can download, install and set the "lc3_clean" theme as default for
Drupal (http://drupal.org/sandbox/litecommerce/1236686).

If you skip this step, you should tweak the CSS files in your Drupal theme to
match the LiteCommerce blocks to the design of your website.

5) Enable the "LC Connector" module in your Drupal-based website.

6) Configure it (see below).


4. Configuration
-----------------

1) Sign in as Drupal administrator, open the module configuration page
("Modules" -> "LC Connector" -> "Configure") and specify the path to your
LiteCommerce 3 installation directory.

The path should be a relative to the Drupal installation directory.

For example, if you installed Drupal into "/u/user-name/public_html/drupal" and
LiteCommerce 3 into "/u/user-name/public_html/litecommerce", the path should be
"../litecommerce".

Another example. If you installed Drupal into "/u/user-name/public_html/drupal"
and LiteCommerce 3 into
"/u/user-name/public_html/drupal/modules/lc_connector/litecommerce", the path
should be "./modules/lc_connector/litecommerce".

If the module can't find LiteCommerce 3 under the specified path, it will
display an error message on the configuration page.

2) Click the "Synchronize user accounts" button to pass the information on
Drupal user accounts to LiteCommerce, and vice versa. If you skip this step you
won't be able to sign in to the LiteCommerce back end with your Drupal
administrator account.


5. Adding e-commerce blocks to your website
--------------------------------------------

1) Sign in as Drupal administrator, open the block management page ("Structure"
-> "Blocks") and then click on the "Add block" link. Now, select the
"LiteCommerce widget" option in the "Block type" field, then, in the "LC widget
details" section, choose the LiteCommerce block you would like to add and
specify its options.

2) Specify the other options, typical for the regular Drupal blocks, and move
the block to the theme region.

3) Do the same for all other LiteCommerce blocks you need on your website.


6. Known issues
----------------

- Since LiteCommerce 3 works with MySQL only (other database engines are not
  supported yet), your Drupal installation should use a MySQL database.


7. Contacting us
-----------------

Please use the Drupal issue tracker for sending us your feedback:
http://drupal.org/project/issues/


Thank you for using LiteCommerce and the "LC Connector" module!

