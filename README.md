# [Addon](https://github.com/trendoman/Przelewy24) Przelewy24

Process payments via [Przelewy24](https://www.przelewy24.pl/) in CouchCMS.

Featuring only 2 tags and a few editable fields, addon will take care of basic payment transactions with Przelewy24 payment processing.

Documentation: [https://developers.przelewy24.pl](https://developers.przelewy24.pl/); Sandbox Panel: [https://sandbox.przelewy24.pl/panel](https://sandbox.przelewy24.pl/panel)

## Tag *`<cms:przelewy24_paylink />`*

First tag creates a link with a token which customer must click and visit Przelewy24 page with selection of payment options (banks etc).

Example link: [`https://sandbox.przelewy24.pl/trnRequest/A44818E6C0-26AC83-8DE9D6-CFC43215A0`](https://sandbox.przelewy24.pl/trnRequest/A44818E6C0-26AC83-8DE9D6-CFC43215A0)

```xml
<cms:przelewy24_paylink
  sandbox = '1'
  debug = '1'
  regulationAccept = '0'
  return_url = 'https://site.web/users/profile.php'
  status_url = 'https://site.web/order.php'
  description = "Zamówienie nr. 1005"
  amount = '333.50'
  shipping = '30'
  into = 'mylink'
/>

<cms:if mylink>
  <a href="<cms:show mylink />" target="_blank">PAY</a> (<cms:show mylink />)
</cms:if>
```

Place the code above on the appropriate page and the link to payment page will be generated. Essentially, tag sends to P24 a few identification fields and receives a token, which is added to the end of the link. Now, in P24 parlance, the *transaction is registered* with the processing company, a token is assigned to it and system is waiting for the user action i.e. visit the link. If user is not acting, nothing bad happens. Links may be regenerated unlimited times. **NOTE:** do not put too much stress on their servers, register transaction only when the order is placed by the customer.

*Transactions* in CouchCMS will be represented by cloned pages of a clonable template.

### Tag parameters

* **sandbox** - ***1*** or ***0*** *(default)*, can switch between live and demo processing host.
* **debug** - ***1*** or ***0*** *(default)*, a few messages or errors will be logged to file **`p24_log.txt`.**
* **regulationAccept** - ***1*** or ***0*** *(default)*, acceptance of Przelewy24 regulations (search explanation on doc page[^1])
* **return_url** - URL address to which customer will be redirected when transaction is complete
* **status_url** - URL address to which transaction status will be send (similar to PayPal, this URL is where the processor is placed)
* **description** - Transaction description, visible on payment page via dropdown menu 'About'.
* **amount** - Transaction amount expressed in normal currency units, e.g. "123.34"
* **shipping** - Delivery cost
* **into** - Takes a name of a new variable that will be created and populated with a link instead of printing it (see example above)

A few defaults, that are statically set in code and not changed via params:

- currency: ***PLN***
- country: ***PL***
- language: ***pl*** – different language can be set via dropdown on payment page
- waitForResult: ***true*** – Parameter determines wheter a user should wait for result of the transaction in the transaction service and be redirected back to the shop upon receiving confirmation or be redirected back to the shop immediately after payment.
- encoding: ***UTF-8***

[^1]: [https://developers.przelewy24.pl/index.php?en#tag/Transaction-service-API/paths/~1api~1v1~1transaction~1register/post](https://developers.przelewy24.pl/index.php?en#tag/Transaction-service-API/paths/~1api~1v1~1transaction~1register/post)

---

Tag must be able to find and use your identification fields. Addon expects them placed in any template of your choice, even in 'globals' sections, where you will fill them with data provided from Przelewy24 Admin Panel. In a sample transaction template (`przelewy24.example-template.php`), most important editable fields are placed in 'globals' section.

- **przelewy24_merchantid** - Merchant ID
- **przelewy24_crc** - CRC Key
- **przelewy24_reportkey** - API Key
- **przelewy24_channel** - which payment ways you allow for the customer
- **przelewy24_email** - your email where reports about transactions will be sent to

## Template *`przelewy24.example-template.php`*

A clonable template holds each *transaction* as a cloned page. **[ADDON "Sequential ID"](https://www.couchcms.com/forum/viewtopic.php?f=8&t=11372)**[^7] is used to automatically increment transaction number. Przelewy24 demands unique transaction numbers, so even if admin removes some cloned pages in template, the transaction number will keep incrementing thanks to that UID addon.

[^7]: [https://www.couchcms.com/forum/viewtopic.php?f=8&t=11372](https://www.couchcms.com/forum/viewtopic.php?f=8&t=11372)

Most important editable field in each cloned page is, therefore, **przelewy24_sessionid**.

Every field in cloned pages is filled automatically, but the fields in 'globals' section must be filled by Admin manually, with info from P24.

A few comfortable customizations are used for the form-view (some fields hidden, preview links auto-generated) and list-view (custom sorting and two dropdown fields to filter cloned pages by year / month). See the screenshots.

## Tag *`<cms:przelewy24_processor />`*

Tag has two parameters and does a validation / verification of payment similar to the **cms:paypal_processor**[^6] tag.

When customer pays, the transaction must be verified. Read more about it in docs[^2]

[^2]: [https://developers.przelewy24.pl/index.php?en#tag/Transaction-service-API/paths/~1api~1v1~1transaction~1verify/put](https://developers.przelewy24.pl/index.php?en#tag/Transaction-service-API/paths/~1api~1v1~1transaction~1verify/put)

[^6]: [https://docs.couchcms.com/tags-reference/paypal_processor.html](https://docs.couchcms.com/tags-reference/paypal_processor.html)
---

P24 suggests to filter requests to page with processing by ip-address[^3].

[^3]: [https://developers.przelewy24.pl/index.php?en#section/IP-server-addresses](https://developers.przelewy24.pl/index.php?en#section/IP-server-addresses)

At the moment, they have following ranges and static ipv4 addresses:

```
91.216.191.181 – 91.216.191.185,
5.252.202.255 , 5.252.202.254
```

Here is how this info can be used, thanks to new code[^4][^5] in public repositories –

```xml
<cms:if
  k__ip eq '5.252.202.255' ||
  k__ip eq '5.252.202.254' ||
  "<cms:call 'is-ip-within' ip=k__ip range='91.216.191.181 - 91.216.191.185' />"
  >
  <cms:przelewy24_processor sandbox='1' debug='1' />
</cms:if>
```

During debug time, you may add a line for localhost e.g. `k__ip eq '127.0.0.1' ||` to perform POST requests by cURL via terminal.

[^4]: **[Tweakus-Dilectus Variables » k__ip](https://github.com/trendoman/Tweakus-Dilectus/tree/main/anton.cms%40ya.ru__variables-new/k__ip)**

[^5]: **[Cms-Fu Validate Funcs » is-ip-within](https://github.com/trendoman/Cms-Fu/tree/master/Validate/is-ip-within)**

### Tag parameters

* **sandbox** - ***1*** or ***0*** *(default)*, can switch between live and demo processing host.
* **debug** - ***1*** or ***0*** *(default)*, a few messages or errors will be logged to file **`p24_log.txt`.**

## Installation

Suppose, the repository is cloned to `/#Przelewy24`, then add following line to `/couch/addons/kfunctions.php` –

```php
require_once( K_SITE_DIR .'#Przelewy24/przelewy24.php' );
```

Rename template `przelewy24.example-template.php` => `przelewy24.php`, place it where you wish and register it in Couch admin-panel. Note, that Addon will find template automatically by the editable field name, so you are free to rename template to whatever desired or place in a subfolder.

Fill in settings in template's "Manage Globals" section.

## Support

[![Mail](https://img.shields.io/badge/gmail-%23539CFF.svg?&style=for-the-badge&logo=gmail&logoColor=white)](mailto:"Anton"<tony.smirnov@gmail.com>?subject=[GitHub])
