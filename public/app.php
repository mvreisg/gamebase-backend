<?php
	namespace Mvreisg\GamebaseBackend;

	use Dotenv;
	use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
	use Mvreisg\GamebaseBackend\Presentation\Routers\DefaultRouter;
	use Mvreisg\GamebaseBackend\Presentation\Routers\GameGenreRouter;
	use Mvreisg\GamebaseBackend\Presentation\Routers\GamePlatformRouter;
	use Mvreisg\GamebaseBackend\Presentation\Routers\GenreRouter;
	use Mvreisg\GamebaseBackend\Presentation\Routers\GameRouter;
	use Mvreisg\GamebaseBackend\Presentation\Routers\PlatformRouter;

	include_once("./../vendor/autoload.php");	

	Dotenv\Dotenv::createImmutable("./../")->load();

	$app = new HttpApplication();

	$defaultRouter = new DefaultRouter();
	$gameRouter = new GameRouter();
	$gameGenreRouter = new GameGenreRouter();
	$gamePlatformRouter = new GamePlatformRouter();
	$genreRouter = new GenreRouter();
	$platformRouter = new PlatformRouter();

	$defaultRouter->register($app);
	$gameRouter->register($app);
	$gameGenreRouter->register($app);
	$gamePlatformRouter->register($app);
	$genreRouter->register($app);
	$platformRouter->register($app);

	$app->run();
?>