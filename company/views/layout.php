<!DOCTYPE html>
<html>    
    <head>
        <title><?php echo $title ?> Page</title>
        <meta charset="UTF-8">
        <meta name="author" content="Lucas Santana">        
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <link rel="stylesheet" href="views/css/screen.css" type="text/css" media="screen">
    </head>
    <body>			
        <header role="banner">
            <div id="header_bar"></div>
        </header>

        <div>
            <?php require_once('routes.php'); ?>
        </div>
    </body>
</html>