# Legalesign-PHP

A PHP-based interface to Legalesign.

## Use

Include it with Composer:

`composer require tylermenezes/legalesign-php`

### Set your credentials
You can view your API credentials at the bottom of [your user page](https://legalesign.com/acc/settings/).
Non-sandboxed use currently requires a paid plan, but you can test your integration in the sandbox. (When in sandbox
mode, you will only be able to send requests to five emails you specify on your profile page.)

You can set your credentials like so:

```php
Legalesign\Api::credentials('ffffffffffffffffffffffffffff', '000000000000000000000000000000000000000000');
```

If you use Laravel, credentials will automatically be pulled from a `legalesign.php` config file, if one exists. The
format of this file should be:

```php
return [
    'userid' => 'ffffffffffffffffffffffffffffff',
    'secret' => '000000000000000000000000000000000000000000'
];
```

### Create a signer
```php
$me = new Legalesign\Signer;
$me->firstName = 'Tyler';
$me->lastName = 'Menezes';
$me->email = 'tylermenezes@protonmail.ch';
```

### Create a signing request

```php
$document = Legalesign\Document::create()
    ->name('Waiver of Liability')
    ->group('mygroup')
    ->addSigner($me)
    ->sendWithTemplatePdf('mytemplate');
```

The `Document::create()` method actually returns a `Document\SigningRequest` object, which will allow you to configure
your signing request. As shown above, you should configure properties by chaining method calls with the names of the
properties you'd like to set.

The following properties are supported:

  - `groupId` (required): the ID of the group to which the document should belong. If you're not sure what this ID is, the easiest way to find it is to hover over your company name in the header. The group ID will be in the last part of the URL.
  - `name` (required): the company name.
  - `userId`: the ID of the user from which the document should be sent. You can leave this blank for the default.
  - `appendLegalesignValidationInfo` (default true): If true, a page will be added to the document with audit logs.
  - `autoArchive` (default true): Automatically archive the document after completion. This mostly just hides it from the dashboard.
  - `signaturesOnAllPages` (default false): If true, show signatures on all pages. Otherwise, only show them on the last page.
  - `certify` (default true): If true, Legalesign should certify the PDF. This is recommended.
  - `signInOrder` (default true): If true, the second signer cannot sign until the first has signed, etc. Signing order is the order in which you add signers.
  - `password` (default null): If set, the PDF will be password protected with the set password.
  - `storePassword` (default false): If a password is set, this affects whether Legalesign saves the password.
  - `footer`, `footerHeight`, `header`, `headerHeight` (all default null): If set, these control the footer and header.

You can also add signers and cc'ers (people who will be cc'd on all emails) with the `addSigner` and `addCc` methods.

To actually send the document, end the chain with one of the following three methods:

  - `sendWithHtml($html)`: Specifies the document to send in HTML, and sends it.
  - `sendWithTemplateHtml($template)`: Specifies an HTML template to use for the document, and sends it.
  - `sendWithTemplatePdf($template)`: Specifies a PDF template to use for the document, and sends it.

All of these methods return a `Document`, of which you should generally save the `id`.

### Retrieve an existing signing request, and try to download the executed agreement.
```php
$document = Legalesign\Document::find('my-document-id');
if ($document->downloadReady) {
    file_put_contents('/tmp/test.pdf', $document->getPdf());
} else {
    echo 'Document is not yet ready!';
}
```
