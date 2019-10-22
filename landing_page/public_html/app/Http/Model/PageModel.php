<?php


namespace Whiskey\Bourbon\App\Http\Model;


// use Whiskey\Bourbon\App\Facade\Captcha;
use Whiskey\Bourbon\App\Facade\Db;
use Whiskey\Bourbon\App\Facade\Input;
use Whiskey\Bourbon\App\Http\Controller\PageController;
use Whiskey\Bourbon\App\Http\MainModel;
use Whiskey\Bourbon\Instance;
use Whiskey\Bourbon\Validation\Handler as Validator;


/**
 * PageModel class
 * @package Whiskey\Bourbon\App\Http\Model
 */
class PageModel extends MainModel
{

   
    /**
     * Validate POST data
     * @param Validator $validator Validator object
     * @param string    $action    Requested controller action
     * @param array     $slugs     Array of URL slugs
     */
    public function _validate($validator, $action = '', array $slugs = [])
    {
        if ($action == 'submit')
        {
            $validator->add('car_reg')->type('MIN_LENGTH')->compare(4)->errorMessage('Please enter a valid car registration');
            $validator->add('diet')->type('MIN_LENGTH')->compare(2)->errorMessage('Please ensure your dietary requirements are correct');

            // if (!Captcha::isValid())
            // {
            //     $validator->addError('g-recaptcha-response', 'Please confirm you are not a robot');
            // }
        }
    }

    /**
     * Logic to carry out if validation from _validate() fails
     * @param Validator $validator Validator object
     * @param string    $action    Requested controller action
     * @param array     $slugs     Array of URL slugs
     */
    public function _onValidationFail($validator, $action = '', array $slugs = [])
    {
        $controller = Instance::_retrieve(PageController::class);

        /*
         * Show errors as a flash message on the next view
         */
        $validator->showErrors();

        /*
         * Return POST data to repopulate the landing page form
         */
        if ($action == 'submit')
        {
            $controller->_with($_POST);
            $this->_response->redirect(PageController::class, 'attending');
        }
    }

    /**
     * saveAttending
     *
     * @param  mixed $name
     * @param  mixed $email
     * @param  mixed $car_reg
     * @param  mixed $diet
     * @param  mixed $attending
     *
     * @return void
     */
    public function saveAttending($name, $email, $car_reg, $diet, $attending, $evening, $overnight)
    {

        //delete duplicate email address
        Db::build()->table('attendance')
            ->where('email', $email)
            ->delete();

        //delete from attendance also, we only have one email now
        Db::build()->table('nonattendance')
            ->where('email', $email)
            ->delete();

        //insert
        Db::build()->table('attendance')
            ->data('name', $name)
            ->data('email', $email)
            ->data('attending', $attending)
            ->data('diet', $diet)
            ->data('car_reg', $car_reg)
            ->data('evening_meal', $evening)
            ->data('staying_overnight', $overnight)
            ->insert();

    }

    /**
     * saveNotAttending
     *
     * @param  mixed $name
     * @param  mixed $email
     * @param  mixed $attending
     *
     * @return void
     */
    public function saveNotAttending($name, $email, $attending)
    {

        //delete duplicate email address
        Db::build()->table('nonattendance')
            ->where('email', $email)
            ->delete();

        //delete from attendance also, we only have one email now
        Db::build()->table('attendance')
            ->where('email', $email)
            ->delete();

        //insert
        Db::build()->table('nonattendance')
            ->data('name', $name)
            ->data('email', $email)
            ->data('attending', $attending)
            ->insert();
            
    }
}