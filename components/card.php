<?php
function renderCard($title, $content) {
    echo "
    <div class='bg-white p-8 rounded-lg shadow-md w-full max-w-sm'> 
      <h1 class='text-2xl font-bold mb-6 text-center'>{$title}</h1>

      <div>{$content}</div>
    </div>
    ";
}
?>

