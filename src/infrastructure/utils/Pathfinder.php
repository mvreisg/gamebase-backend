<?php
	namespace Gamebase\Infrastructure\Utils;

	class Pathfinder 
	{
		public static function find(string $relativePath): string{
			$explodedRelativePath = explode(DIRECTORY_SEPARATOR, $relativePath);
			$fullPathParts = [];
			$fullPathParts[] = ROOT_DIRECTORY;
			foreach($explodedRelativePath as $partOfRelativePath){
				$fullPathParts[] = $partOfRelativePath;
			}
			return join(DIRECTORY_SEPARATOR, $fullPathParts);
		}
	}
?>