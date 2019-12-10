<?php
// Routes

require __DIR__ . '/../vendor/autoload.php';
use Blog\Model\entriesModel;
use Blog\Model\commentsModel;
use Blog\Model\tagsModel;

//get the index page 
$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    //init the entries model obj and read the data
    $db = new entriesModel();
    $dbModel = new tagsModel();
    $tags = [];

    $posts = $db->getBlog();
    
    //get the tags under the same entry
    foreach($posts as $post){
        $tags[] = $dbModel->fetchTags($post["id"]);
    }
    //var_dump($tags);
    // Render index view
    return $this->renderer->render($response, 'index.twig', [
        'posts'=>$posts,
        'tags' => $tags
    ]);

})->setName('/');


//get the edit page 
$app->get('/edit/{slug}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    $db = new entriesModel();
    $result = $db->getBlogBySlug($args['slug']);

    // Render view
    return $this->renderer->render($response, 'edit.twig',['result' => $result]);
})->setName('edit');

//post the edit page 
$app->post('/edit/{slug}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    $data = $request->getParsedBody();

    //init the db object
    $db = new entriesModel();
    $db->editBlog($data, $args['slug']);
    
    //redirect back to index page 
    return $response->withRedirect('/', 301);

})->setName('edit');

//get the add page 
$app->get('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'new.twig', $args);
})->setName('new');


//post the add page 
$app->post('/new', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("POST Slim-Skeleton '/new' route");

    $data = $request->getParsedBody();

    //init the db object
    $db = new entriesModel();
    $db->addBlog($data);
    
    //redirect back to index page 
    return $response->withRedirect('/', 301);

})->setName('new');


//get the detail page 
$app->get('/{slug}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    $db = new entriesModel();
    $commentObj = new commentsModel();
    $result = $db->getBlogBySlug($args['slug']);
    $comments = $commentObj->getComments($args['slug']);
    // Render index view
    return $this->renderer->render(
        $response, 
        'detail.twig', 
        [
            'result' => $result,
            'comments' => $comments
        ]
        );
})->setName('slug');


//post the detail page for page commenting
$app->post('/{slug}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    $data = $request->getParsedBody();
    $commentObj = new commentsModel();

    $commentObj->addComment($data, $args['slug']);
    
    //redirect back to index page 
    return $response->withRedirect($args['slug'], 301);
})->setName('comment');

//get the index page filtered by tags
$app->get('/filtered/{tag}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    //init the entries model obj and read the data
  
    $dbModel = new tagsModel();

    $posts = $dbModel->tagsFiltered($args['tag']);
    //var_dump($posts);
    //get the tags under the same entry
    foreach($posts as $post){
        $tags[] = $dbModel->fetchTags($post["id"]);
    }
 
    // Render index view
    return $this->renderer->render($response, 'filtered.twig', [
        'posts'=>$posts,
        'tags' => $tags
    ]);

})->setName('filtered');

//post the delete page 
$app->get('/delete/{id}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    //init the db object
    $db = new entriesModel();
    $db->deleteBlog($args['id']);
    
    //redirect back to index page 
    return $response->withRedirect('/', 301);

})->setName('delete');