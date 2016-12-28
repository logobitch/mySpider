<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <?php foreach($lists as $list) { ?>
        <h1><?php echo $list['title'];?></h1><small><?php echo $list['item_id'];?></small>
        <p><?php echo $list['content'];?></p>
    <?php } ?>
</body>
</html>