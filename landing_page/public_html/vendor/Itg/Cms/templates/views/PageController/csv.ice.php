<?php

use Itg\Cms\Http\Controller\PageController;


?>

<div class="row">

    <div class="col-md-12">
        <h2>Data Submissions</h2>
    </div><!-- col-md-12 -->

    <div class="col-md-12">
        <h4>Download CSV Files</h4>
        <a href="{{ $_helper->_link(PageController::class, 'attendees') }}"><button>Download Attendees</button></a>
        <a href="{{ $_helper->_link(PageController::class, 'nonattendees') }}"><button>Download Non-Attendees</button></a>
    </div><!-- col-md-12 -->

</div><!-- row -->