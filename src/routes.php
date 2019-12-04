<?php
// Routes

require __DIR__ . '/../vendor/autoload.php';
use Blog\Model\entriesModel;

//get the index page 
$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    //init the entries model obj and read the data
    $db = new entriesModel();
    $posts = $db->getBlog();
    // Render index view
    return $this->renderer->render($response, 'index.twig', ['posts'=>$posts]);
});


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
    //$db->createSlug();

    //redirect back to index page 
    return $response->withRedirect('/', 301);

})->setName('new');


//get the detail page 
$app->get('/blog/{title}', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'datail.twig', ['title' => $args['title']]);
})->setName('blog');
