YiiMailing
==========

Mailing Subsystem for Yii

This Exstention allows you to send emailes from Yii Application throw diffrent transports (mail(), SMTP or create your own), 
using templates and layouts. Also supported batch sending and emailes debugging.

## Features

* Using diffrent transports. PHP mail() function, SMTP transport or you can create your own quickly.
* Using templates and layouts.
* Working with Attaches
* Batch sending (without re-opening connection for SMTP transport)
* Debugging. You can change transport to debug, and no one real email will be sended, they will be showed on the page in debug widget.
* Easy to use and extend

## Installation
To start using it, copy **Mailing** folder to **Application.Extension** in you project then add next lines to you `config/main.php`:
```php
 'components' => array(
  ...
    'mail' => array(
        'class' => 'ext.Mailing.Mailer',
        'emailDefaults' => array(
            'sender' => 'Any Sender name <robot@sender.domain>',
            //'layout' => 'ext.Mailing.views.layout', //this is used by default. You can set any other layout for email letters
        ),
        'transport' => array(
            'class' => 'SmtpTransport' //DebugTransport, PhpTransport, SmtpTransport
        )
    ),
  ...
    )
```

## Usage and examples

You can access Mailer class using this:
```php
Yii::app()->mail
```

Basicly, you have to do next steps:

1. Create a letter
2. Add letter to Mailer
3. Call `Send()` method of Mailer. 

But this workflow is redundantly if you want to just send one email.

So we have a shortcut methods:

### Building letter from string

```php
$mailer = Yii::app()->mail;
$letter = $mailer->createLetterFromString($subject, $body, $recipient);
$mailer->send();
```

### Building letter from template and layout

```php
$mailer = Yii::app()->mail;
$letter = $mailer->createLetterFromView($subject, $view, $params=array(), $recipient);
$mailer->send();
```

Where `$view` is a name, or alias of the view file name, and `$params` is a array of view params.

Methods listed above are factories. They return an EmailLetter object. 
That means you can build letter more flexibly using methods and properties as described below.

### Building letter manually
You can create Letter manually by directly instantiating EmailLetter object, but keep in mind, if you did that any emailDefault setted in the config will NOT work.

```php
//Create an EmailLetter
$letter = new EmailLetter();
$letter->subject = "Test letter";
$letter->body = "Test Body";
$letter->sender = "test@test.com";
$letter->recipient = "recipient@test.com";

//add attach
$cid = $letter->addAttach($file_path);

//Send
Yii->app()->mail->sendOne($letter);

//Or if you have more than one letter(or many diffrent recipients) you can use batch sending
Yii->app()->mail->add($letter);
Yii->app()->mail->add($letter2);
Yii->app()->mail->add($letter3);

Yii->app()->mail->send()
```

## Batch sending
As you can see above batch sending its easy. Just create so many letters as you want and call `send()` after that. 

```php
$mailer = Yii::app()->mail;

//Create some letters
$mailer->createLetterFromView($subject, $view, $params=array(), $recipient);
$mailer->createLetterFromString($subject, $body, $recipient);

//or create manually
$mailer->add(new EmailLetter($subject, $body, $recipient, $sender));

//and when you're finished call send() method
$mailer->send();
```

## Attaches
You can add attach by calling `EmailLetter::addAttach()` method. A few examples:
```php
$mailer = Yii::app()->mail;

//Create letter using factory
$letter = $mailer->createLetterFromView($subject, $view, $params=array(), $recipient);
$cid = $letter->addAttach($file_path, $file_name);

//or create letter manually
$letter = new EmailLetter($subject, $body, $recipient, $sender);
$cid = $letter->addAttach($file_path);
$mailer->add($letter);

//and when you're finished call send() method
$mailer->send();
```

`EmailLetter::addAttach()` returns a `cid`. Cid used to identificate attach in email. It can be usefull for embedding images
```php
  $letter = new EmailLetter($subject, $body, $recipient, $sender);
  
  $letter->body = preg_replace_callback('/src=["\'](.*?)["\']/', function($matches) use ($letter) {
              $cid = $letter->addAttach($matches[1]);
              return "src='cid:$cid'";
          }, $body);

```

## Configuring
You can configure this extension like an usual Yii component.

**Mailing class** has 2 important options: 

| Property      | Type 		| Description   |
| ----- | ---- |--- |
| transport     | array() | This is transport class definition array. You can set you own transport, or use predefined. Transport is a PHP Class implemented `IMailerTransport` interface. All items from this array will by used for instantiating of a Transport class.
| emailDefaults | array() | This is email class definition array. Here you can inject other class for Letters, or set defaults. For example default sender, view, layout or subject.

###Transports
Transports is used for delivering mailes. Here we have a few transports.

#### SMTP Transport.
This transport is used for sending mailes via SMTP server. Basicly is sends via local smtp server that's why it doesn't support authentification, but it's easy to implement in future.

| Property      | Type 		| Default  | Description   |
| ----- | ---- |---- |--- |
| server     | string | 127.0.0.1 | SMTP Server host name or ip adress.
| serverPort | int | 25 | Server port
**Example:**
```php
        'transport' => array (
            'class' => 'SmtpTransport',
            'server' => '192.168.1.1',
            'serverPort' => '101'
        )
```

#### PHP Transport.
This transport is used for sending mailes via PHP mail() function. Strictly not recomended to use this transport for batch mailing in unix systems.
It doesn't have any special options.
**Example:**
```php
        'transport' => array (
            'class' => 'PhpTransport', 
        )
```

#### Debug Transport.
This transport is used for debugging purposes. It doesn't delivery emailes instead, it stored them in the `Yii::app()->user->setFlash()`. 
It works together with DebugWidget, which extracts and shows debug messages from `Yii::app()->user->getFlash()`.
**Example:**
```php
        'transport' => array (
            'class' => 'DebugTransport', 
        )
```
## Debugging
As described above you have to set Debug transport, and also put debug widget somewhere in the page. And it will be work.
**Example:**
```php
      //config/main.php
        'transport' => array (
            'class' => 'DebugTransport', 
        )
        
     //in the bottom of views/layouts/main.php
     $this->createWidget('ext.Mailing.DebugWidget');
```

## Extending
YiiMailing can be extended by writting you own transport, email message classes or extending Mailing component. 
You just need to inject new classes in the config. 

**Example:**
```php
 'components' => array(
  ...
    'mail' => array(
        'class' => 'MyMailer', //extended from ext.Mailing.Mailer class
        'emailDefaults' => array(
          'class' =>  'MyEmailLetter' //extended from EmailLetter class
        ),
        'transport' => array(
            'class' => 'MyTransport' //implement IMailerTransport interface
        )
    ),
  ...
    )
```

### Writing own Transport
If you want write you own transport, you have to extend it from `IMailerTransport` interface and implement methods from this interface. 
For example you can write queued mailing system:

```php
class QueuedTransport implements IMailerTransport {
 
    public function init() {}
    
    /**
     * 
     * @param string $from Sender email (only email!)
     * @param string $recipient recipient Email (only email!)
     * @param string $headers prepared headers
     * @param string $body letter body
     */
    public function send($from, $recipient, $headers, $body) {
      //save message in the DB queue
       $queue = new Queue();
       $queue->from = $from;
       $queue->recipient = $recipient;
       $queue->headers = $headers;
       $queue->body = $body;
       
       $queue->type = Queue::EMAIL_MESSAGE;
       $queue->createDate = 'NOW()';
       $queue->save();
    }
}

//Some example code wich called from crontab
$queue = Queue::model()->findAllByAttributes(array('type' => Queue::EMAIL_MESSAGE));
foreach ($queue as $task) {
   //processing each task 
}

```

