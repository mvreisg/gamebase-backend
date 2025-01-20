<?php
	namespace Gamebase;

	use Gamebase\Infrastructure\Http\HttpApplication;
	use Gamebase\Presentation\Routers\DefaultRouter;
	use Gamebase\Presentation\Routers\GameGenreRouter;
	use Gamebase\Presentation\Routers\GamePlatformRouter;
	use Gamebase\Presentation\Routers\GenreRouter;
	use Gamebase\Presentation\Routers\GameRouter;
	use Gamebase\Presentation\Routers\PlatformRouter;

	include_once("./../src/infrastructure/http/HttpApplication.php");
	include_once("./../src/presentation/routers/DefaultRouter.php");
	include_once("./../src/presentation/routers/GameGenreRouter.php");
	include_once("./../src/presentation/routers/GamePlatformRouter.php");
	include_once("./../src/presentation/routers/GameRouter.php");
	include_once("./../src/presentation/routers/GenreRouter.php");
	include_once("./../src/presentation/routers/PlatformRouter.php");

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