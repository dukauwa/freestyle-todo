<?php

require '../settings.php';
require '../vendor/autoload.php';

$action = isset($_GET['page']) ? $_GET['page'] : 'home';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Todo Page</title>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
<?php

if ($action === 'add')
{
    ?>

    <h1>Add New Todo [<a href="/?page=list">List</a>]</h1>

    <form method="POST" action="<?= BASE_URL ?>/?page=save">
        <label>What Do you Want To Do</label><br />
        <input type="text" name="todo" autofocus="">
        <input type="submit" value="Add" class="btn btn-primary">
    </form>

    <?php
}


else if ($action === 'save')
{
    $gump = new GUMP;

    $_POST = $gump->sanitize($_POST);

    $gump->validation_rules(array(
        'todo' => 'required|max_len,50|min_len,2',
    ));

    $gump->filter_rules(array(
        'todo' => 'trim|sanitize_string',
    ));

    $validated_data = $gump->run($_POST);

    if($validated_data === false) {
        echo $gump->get_readable_errors(true);
        exit;
    }

    $todo = [
        'title' => $validated_data['todo'],
        'created_at' => date('d-M-y h:i:s'),
        'done' => false
    ];

    // Get existing todos...
    $todos = fetchTodosFromDatabase();

    // Add the array to the list of arrays
    array_unshift($todos, $todo);

    saveAndRedirect($todos);
}




else if ($action === 'list')
{
    $todos = fetchTodosFromDatabase();

    ?>
    <h1>Available Todos [<a href="/?page=add">Add</a>]</h1>

    <?php if (empty($todos)): ?>
    <span>Whoo Hoo! You have nothing to do.</span>
    <?php endif; ?>

    <ul>
    <?php
    foreach ($todos as $key => $todo) {
        echo '<li>';

        $htmltag = $todo['done'] ? 'strike' : 'span';

        echo sprintf( '<%s>'. $todo['title'] .'</%s> ', $htmltag, $htmltag );

        if ($todo['done'] === false) {
            echo '[<a href="'.BASE_URL.'/?page=do&id='.$key.'">Complete</a>]';
        }

        echo '[<a href="/?page=delete&id='.$key.'" style="color:red">Delete</a>]';

        echo  '</li>';
    }
    ?>
    </ul>
    <?php
}




else if ($action === 'do')
{
    $id = $_GET['id'];

    $todos = fetchTodosFromDatabase();

    if (isset($todos[$id])) {
        $todos[$id]['done'] = true;
    }

    saveAndRedirect($todos);
}


else if ($action === 'delete')
{
    $id = $_GET['id'];

    $todos = fetchTodosFromDatabase();

    unset($todos[$id]);

    saveAndRedirect($todos);
}



function fetchTodosFromDatabase() {
    return json_decode(file_get_contents(STORAGE_DIR.'/todos.json'), true);
}


function saveAndRedirect($todos) {
    file_put_contents(STORAGE_DIR.'/todos.json', json_encode($todos));
    header('Location: /?page=list');
    exit;
}
?>
</body>
</html>