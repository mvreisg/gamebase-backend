<?php
    namespace Gamebase\Infrastructure\Repositories;

    use PDO;
    use PDOException;
    use Gamebase\Domain\Repositories\GameRepositoryInterface;
    use Gamebase\Domain\Entities\Game;
    
	include_once("./../src/domain/repositories/GameRepositoryInterface.php");

    class MariaDBGameRepository implements GameRepositoryInterface 
    {
        private PDO $pdo;

        public function __construct(PDO $pdo)
        {
            $this->pdo = $pdo;
        }

        public function insert(Game $game): Game 
        {
            try
            {
                $this->pdo->beginTransaction();

                $name = $game->getName();

                $insertStatement = $this->pdo->prepare("INSERT INTO game (name) VALUES (:name);");
                $insertStatement->execute([":name" => $name]);

                $lastInsertId = intval($this->pdo->lastInsertId());

                $selectStatement = $this->pdo->prepare("SELECT * FROM game WHERE id = :id;");
                $selectStatement->execute([":id" => $lastInsertId]);

                $gameFetchResult = $selectStatement->fetch();

                $this->pdo->commit();

                $newGame = new Game();                                
                $newGame->setId($gameFetchResult["id"]);
                $newGame->setName($gameFetchResult["name"]);

                return $newGame;
            }
            catch (PDOException $e)
            {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        public function edit(Game $game): bool
        {
            $id = $game->getId();
            $name = $game->getName();    

            try 
            {
                $statement = $this->pdo->prepare("UPDATE game SET name = :name WHERE id = :id;");

                $statement->execute([
                    ":name" => $name,
                    ":id" => $id
                ]);

                $wasItSuccessful = $statement->rowCount() > 0;
                return $wasItSuccessful;
            }
            catch (PDOException $e) 
            {
                throw $e;
            }
        }

        public function delete(int $id): bool 
        {
            return false;
        }

        public function findById(int $id): Game|null
        {
            try 
            {
                $statement = $this->pdo->prepare("SELECT * FROM game WHERE id = :id;");
                $statement->execute([":id" => $id]);

                $result = $statement->fetch();
                
                if ($result === false) 
                {
                    return null;
                }

                $game = new Game();
                $game->setId($result["id"]);
                $game->setName($result["name"]);

                return $game;
            }
            catch (PDOException $e) 
            {
                throw $e;
            }
        }

        public function findAll(): array 
        {
            try
            {
                $statement = $this->pdo->prepare("SELECT * FROM game;");
                $statement->execute();                
                $result = $statement->fetchAll();
                
                $games = [];
                foreach($result as $row) 
                {
                    $game = new Game();
                    $game->setId($row["id"]);
                    $game->setName($row["name"]);
                    $games[] = $game;
                }

                return $games;
            }
            catch (PDOException $e) 
            {
                throw $e;
            }
        }

        public function hasDuplicatedNames(string $name): bool
        {
            try 
            {
                $statement = $this->pdo->prepare("SELECT * FROM game WHERE name = :name;");
                $statement->execute([":name" => $name]);
    
                return $statement->rowCount() > 0;
            }
            catch (PDOException $e)
            {
                throw $e;
            }
        }
    }
?>