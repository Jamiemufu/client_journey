<!DOCTYPE html>

<html lang="en">

    <head>
        <title>ITG - DUNELM</title>
        <meta charset="UdTF-8">
        <meta name="viewport" content="user-scalable=no, width=device-width" />        
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" />
        {{ $_helper->_css('styles.min.css') }}
        {{ $_helper->_css('bootstrap.css') }}
        <!--[if lt IE 9]>
            {{ $_helper->_js('ie8.min.js') }}
        <![endif]-->
    </head>

    <body>

    <div class="flex-container">

        <header>
            <div class="logo">
                <img src="{{ image_dir() }}itg_dunelm.png" width="100%" alt="" />
            </div>
        </header>

        <main class="main" role="main">
                {include:content}
        </main>

    </div>

        {{ $_helper->_ga(isset($_ENV['GA_KEY']) ? $_ENV['GA_KEY'] : '') }}
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        {{ $_helper->_js('bootstrap.min.js') }}
        {{ $_helper->_js('scripts.min.js') }}

    </body>

</html>