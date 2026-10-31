<?php
include 'config.php';

unset($_SESSION['user_id']);
session_regenerate_id(true);
flash('Вы вышли из аккаунта');
redirect('index.php');
