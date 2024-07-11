<?php

include "includes/init.php";


if (isset($_POST['type'])) {
    switch ($_POST['type']) {
        case 'note':
            delete('notes', $db);
            break;
        case 'user':
            delete('users', $db);
            break;
        case 'subject':
            delete('subjects', $db);
            break;
        case 'comment':
            delete('comments', $db);
            break;
        default:
            break;
    }
}
