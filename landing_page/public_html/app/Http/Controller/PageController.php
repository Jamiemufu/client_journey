<?php

namespace Whiskey\Bourbon\App\Http\Controller;

use Whiskey\Bourbon\App\Facade\Input;
use Whiskey\Bourbon\App\Http\MainController;

/**
 * PageController class
 * @package Whiskey\Bourbon\App\Http\Controller
 */
class PageController extends MainController
{

    /**
     * Homepage
     */
    public function home()
    {
        session_destroy();
        session_start();

        $_SESSION['attending'] = Input::get('attending');
        $_SESSION['email'] = Input::get('email');
        $_SESSION['name'] = Input::get('name');

        if (isset($_SESSION['name'])) 
        {
            if ($_SESSION['attending'] === 'y') 
            {
                $this->_response->redirect(static::class, 'attending');
            } else {
                $this->_response->redirect(static::class, 'not_attending');
            }

        } 
        else 
        {
            $this->_response->redirect(static::class, 'error');
        }
    }

    /**
     * 'Attending' page
     */
    public function attending()
    {
        if (isset($_SESSION['name'])) 
        {
            //wait for submit
        } 
        else 
        {
            $this->_response->redirect(static::class, 'error');
        }
    }

    /**
     * 'Not Attending' page
     */
    public function not_attending()
    {
        if (isset($_SESSION['name'])) 
        {
            $name = $_SESSION['name'];
            $email = $_SESSION['email'];
            $attending = 'N';

            $this->_model->saveNotAttending($name, $email, $attending);

            session_destroy();
        }
        else 
        {
            $this->_response->redirect(static::class, 'error');
        }
    }

    /**
     * Script to save data after validated
     */
    public function submit()
    {

        if (isset($_SESSION['name'])) 
        {
            $name = $_SESSION['name'];
            $email = $_SESSION['email'];
            $car_reg = Input::post('car_reg');
            $diet = Input::post('diet');
            $attending = 'Y';
            $evening = Input::post('evening');
            $overnight = Input::post('overnight');

            $this->_model->saveAttending($name, $email, $car_reg, $diet, $attending, $evening, $overnight);

            $this->_response->redirect(static::class, 'thanks');

            session_destroy();
        }
        else 
        {
            $this->_response->redirect(static::class, 'error');
        }

    }

    /**
     * Script to save data
     */
    public function thanks()
    {

        if (!isset($_SESSION['name'])) 
        {
            $this->_response->redirect(static::class, 'error');
        }

        session_destroy();
    }
    /**
     * 'error' page
     */
    public function error()
    {
        session_destroy();
    }

}
