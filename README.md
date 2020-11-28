
## Curbside Pickup Module
Magento 2 module which enable curbside pickup of your order when available for delivery and/or 
scheduled for pick up. The module is made on top of new In Store Delivery method introduced in
Magento 2.4.1 version.

## System Requirements

- PHP 7.4.*
- Nginx/Apache
- Magento 2.4.1 Instance
- MySQL 7 or preferably 8

## Installation

* `composer require imaginationmedia/magento2-curbside-pickup` , otherwise download and place directly in `app/code`
* `php bin/magento setup:upgrade`
* `php bin/magento setup:static-content:deploy`
* `php bin/magento setup:di:compile`

NOTE:
* make sure to setup Cron on server for regular sending order delivery email reminders
* make sure to create 3 Mail Notification Templates in `Marketing->Email Templates` 

## Setup

* `Store->Configuration->Inventory->Sources` - make new source(s) , enable it for in Store and Curbside delivery and make sure Default source is active
* `Store->Configuration->Inventory->Stocks` - make new stocks(s) and assign it to every above create sources
* Enable in store Delivery and Curbside Pick Up methods in: `Store->Configuration->Sales->Delivery Methods`

## Additional Configuration 

* `Store->Configuration->Sales->Sales Emails` - email notifications, Default: Enabled
* `Store->Configuration->Sales->Delivery Methods`- module activation, Default: Enabled
* `Store->Configuration->Sales->Delivery Methods`- threshold (in hours) after order ready for delivery, Default: 6 hrs
* `Store->Configuration->Sales->Delivery Methods`- scheduled Pick Up, Default: Enabled

## Features

#### Storefront: 
* Order Curbside Pickup on scheduled time or after the Order ready for delivery
* Checkout: Order Pick up on willing nearest Store to your location
* Email notifications during a order flow statuses: order accepted, ready and ready for pick up by Customer/Guest
* Customer Curbside Orders History preview page
* Customer Order View Details Page
* Customer/Guest Order Pick up and Tracking page
* CRON Email reminder one hour before scheduled curbside pickup 

#### Magento Admin area:
* Curbside Sales Grid Manager colorized by Order statuses and instant data refresh on change or every minute if no changes
* Curbside Grid: display order info in Modal
* Curbside Order Detail Page

Support
---
Need help setting up or want to customize this extension to meet your business needs? Please email info@imaginationmedia.com.

© Imagination Media LLC. | [www.imaginationmedia.com](https:/imaginationmedia.com)
