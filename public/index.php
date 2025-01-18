<?php
	namespace Gamebase;

	use Exception;
	use Gamebase\Infrastructure\Utils\Pathfinder;
	use Gamebase\Presentation\Http\HttpApplication;
	use Gamebase\Presentation\Routers\DefaultRouter;
	use Gamebase\Presentation\Routers\GameGenreRouter;
	use Gamebase\Presentation\Routers\GamePlatformRouter;
	use Gamebase\Presentation\Routers\GenreRouter;
	use Gamebase\Presentation\Routers\GameRouter;
	use Gamebase\Presentation\Routers\PlatformRouter;

	define("DEVELOPMENT", "development");
	define("PRODUCTION", "production");
	define("ENVIRONMENT", DEVELOPMENT);

	switch(ENVIRONMENT){
		case DEVELOPMENT:
			define("ROOT_DIRECTORY", join(DIRECTORY_SEPARATOR, ["D:", "m", "p", "gamebase-backend"]));
			break;
		case PRODUCTION:
		default:
			throw new Exception("Ambiente não configurado para produção");
	}
		
	define("PATHFINDER_DIRECTORY", join(DIRECTORY_SEPARATOR, [ROOT_DIRECTORY, "src", "infrastructure", "utils", "Pathfinder.php"]));

	include_once(PATHFINDER_DIRECTORY);
	include_once(Pathfinder::find("src/presentation/http/HttpApplication.php"));
	include_once(Pathfinder::find("src/presentation/routers/DefaultRouter.php"));
	include_once(Pathfinder::find("src/presentation/routers/GameGenreRouter.php"));
	include_once(Pathfinder::find("src/presentation/routers/GamePlatformRouter.php"));
	include_once(Pathfinder::find("src/presentation/routers/GameRouter.php"));
	include_once(Pathfinder::find("src/presentation/routers/GenreRouter.php"));
	include_once(Pathfinder::find("src/presentation/routers/PlatformRouter.php"));

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