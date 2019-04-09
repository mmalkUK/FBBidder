<?php
session_start();
if(isset($_POST['likesChange'])){
    if($_POST['likesChange'] == 0){
        $_SESSION['likes']['data'] = false;
    }else{
        $_SESSION['likes']['data'] = 1;
    }
}
?>
