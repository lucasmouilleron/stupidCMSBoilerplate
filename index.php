<?php require_once __DIR__."/libs/tools.php" ?>

<!DOCTYPE html>
<html>
<head>

    <title><?php echo GENERAL_COMPANY?> | page 1</title>
    <meta name="description" content="">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width initial-scale=1.0">
    <link rel="shortcut icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/main.css">

</head>
<body>

    <!-- /////////////////////////////////////////////////////////////// -->
    <div class="container">

        <header>
            <h1><?php echo GENERAL_COMPANY?></h1>
            <nav>
                <a href="index" class="active">page 1</a> <span class="sep">|</span> <a href="page2">page 2</a>
            </nav>
        </header>

        <div class="about">
            texte en dur
            <?php echo getSection("page1-about")?>
        </div>        

        <footer>
            <p>&copy; <?php echo GENERAL_COMPANY ?> | <a href="mailto:<?php echo GENERAL_EMAIL?>"><?php echo GENERAL_EMAIL?></a></p>
        </footer>

    </div>

</body>
</html>