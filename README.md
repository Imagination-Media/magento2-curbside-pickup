
## Curbside Pickup Module
This Curbside Pickup Module enhances the Buy Online Pick-up In Store (BOPIS) functionality added in the Magento 2.4.1 release.  It will support retailers that use Magento Multi-Source Invenetory (MSI) and those that do not use MSI.  The module allows for modifying the Ready for Pickup notification to include a URL that takes the customer to a simplified UI displaying their order information and allowing them to input their car's make/model/color.  The customer will click this link and enter their information upon arrival at the retailer's physical store.  The entry of this information will trigger a notification to the store that will prompt them to deliver the customer's order to their vehicle at curbside.

Magento 2 module which enables curbside pickup of orders when available for delivery and/or 
scheduled for pick up. The module is made on top of new In Store Delivery method introduced in
Magento 2.4.1 version.

## System Requirements

- PHP 7.4.*
- Nginx/Apache
- Magento 2.4.1 Instance
- MySQL 7 or preferably 8

## Installation

* `composer require imaginationmedia/magento2-curbside-pickup` , otherwise download and place it directly in `app/code`
* `php bin/magento setup:upgrade`
* `php bin/magento setup:static-content:deploy`
* `php bin/magento setup:di:compile`
* Make sure to setup Cron on server for regular sending of order delivery email reminders

## Setup

* Enable In-Store Delivery and Curbside Pickup methods in: `Stores->Configuration->Sales->Delivery Methods`
* `Stores->Inventory - Sources` - Add/Update source(s) and enable "Use as Curbside Pickup Location", which requires "Use as Pickup Location" to be enabled too. The Default Source must be a different source as it cannot be used for In-Store or Curbside Pickup.
* `Stores->Inventory - Stocks` - Add/Update stocks(s) and assign it to the appropriate Sources
* Load and save the following three email templates in `Marketing->Email Templates->Add New Template`
  - `ImaginationMedia_CurbsidePickup - Curbside Order Accepted`
  - `ImaginationMedia_CurbsidePickup - Curbside Order Ready`
  - `ImaginationMedia_CurbsidePickup - Curbside Pickup Delivery Reminder`
  
## Additional Configuration 

* `Stores->Configuration->Sales->Sales Emails` - Curbside Order - email notifications, Default: Enabled
* `Stores->Configuration->Sales->Delivery Methods`- Curbside Pickup - module activation, Default: Enabled
* `Stores->Configuration->Sales->Delivery Methods`- Curbside Pickup - Threshold to allow the pickup (time needed before pickup), Default: 6 hrs
* `Stores->Configuration->Sales->Delivery Methods`- Curbside Pickup - Scheduled Pick Up (email reminder), Default: Enabled

## Features

#### Storefront: 
* Order Curbside Pickup at a scheduled time or after the order is ready for pickup
* During Checkout: Able to choose nearest store as a Curbside Pick Up location
* Email notifications during order flow statuses: order accepted, ready, and ready for pick up by Customer/Guest
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
