<?php
// use Whiskey\Bourbon\App\Facade\Captcha;
use Whiskey\Bourbon\App\Http\Controller\PageController;
?>

<div class="title">
    <h1>We are so glad<br />you're joining us</h1>
    <p>If you could kindly let us know the below</p>
</div>

<div class="form">

    <form class="form" method="POST" action="{{ $_helper->_link(PageController::class, 'submit') }}">

        <input type="hidden" name="csrf_token" value="{csrf}" />
        <input type="hidden" name="email" value="{{ $email or '' }}" />

        {include:message}

        <label for="car_reg">
            <p>Car registration (if parking is required)</p>
        </label>

        <input type="text" name="car_reg" id="car_reg" value="{{ $car_reg or '' }}" onblur="validate(this);"  />

        <label for="diet" class="pad">
            <p>If you have any dietary requirements and are attending either afternoon lunch or the evening meal, please tell us below:</p>
        </label>

        <textarea rows="7" name="diet" value="{{ $diet or '' }}" onblur="validate(this);"></textarea>
        
        <div class="divider">
        </div>
        
        <!-- Evening meal attendance? -->
        <div class="submit-container">

            <div class="toggle-btns">

                <div>
                    <p>Will you be attending the evening meal?</p>
                </div>


                <div class="btn-yes-no">
                
                    <input class="btn-yes" type="radio" id="radio-one" name="evening" value="YES" checked/>
                    <label for="radio-one">YES</label>
                    
                    <input class="btn-no" type="radio" id="radio-two" name="evening" value="NO" />
                    <label for="radio-two">NO</label>

                </div>
                
            </div>

        </div>

        <!-- Overnight stay attendance? -->
        <div class="submit-container">

            <div class="toggle-btns">

                <div>
                    <p>Will you be staying overnight?</p>
                </div>


                <div class="btn-yes-no">
                
                    <input class="btn-yes" type="radio" id="radio-three" name="overnight" value="YES" checked />
                    <label for="radio-three">YES</label>
                    
                    <input class="btn-no" type="radio" id="radio-four" name="overnight" value="NO" />
                    <label for="radio-four">NO</label>

                </div>
                
            </div>            

        </div>
        
        <div class="button">
            <button href="#!" type="submit">SUBMIT</button>
        </div>
        
    </form>
</div>