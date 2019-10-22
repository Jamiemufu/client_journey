<?php

// use Whiskey\Bourbon\App\Facade\Captcha;
use Whiskey\Bourbon\App\Http\Controller\PageController;

?>

<div class="row">

    <div class="col-sm-12 main_image text-center">
       <img src="{{ image_dir() }}not_ready.jpg" alt="You're NOT Ready for the Annual Review" />
    </div>

     <div class="col-sm-12 sub_image text-center">      
       <img src="{{ image_dir() }}2018.jpg" alt="2018" />
    </div>

    <div class="col-sm-12 main_content text-center">
        <h3>Sorry you can’t make it, thank you for confirming!</h3>    	
    </div>

</div>