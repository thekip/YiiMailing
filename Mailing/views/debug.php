<div class="emailDebug">
    <h2>Dumping email</h2>
    <p>The email extension is in debug mode, which means that the email was not actually sent but is dumped below instead</p>
    
    <h3>Email</h3>
    <strong>From:</strong> <?php echo CHtml::encode($from)?> <br />
    <strong>To:</strong> <?php echo CHtml::encode($recipient) ?>
   
    <pre><?php echo CHtml::encode($source)?></pre> 
    
</div> 