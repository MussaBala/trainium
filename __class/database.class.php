<?php

/**
 * Document    : __class/database.php
 * Created on  : 20110427
 * Last update : 20170207
 *
 * @author     : Stéphane KOEBERLE (SKE)
 * @copyright  : Link To Business
 * @version    : 0.0.2
 */


/**
 * 20170207 - 0.1.0 (OK)  : Ajout de la prise ne compte de Singleton et utilisation de MYSQLI
 * 20140213 - 0.0.2 (SKE) : Ajout d'un type de résultat dans la méthode query()
 * 20140129 - 0.0.1 (BAA) : Ajout d'un type de résultat dans la méthode query()
 */

if (!defined('APP_STARTED')) {
    define('APP_STARTED', true);
}
$root_path = realpath(__DIR__ . '/..'); 
include_once($root_path . '/config/config.php');

/**
 * Class database
 */
class database
{

	public $ready;
	private $dbhost;
	private $dbuser;
	private $dbpwd;
	private $dbname;
	private $dbh;
	public $debug;

	/**
	 * @var Singleton
	 * @access private
	 * @static
	 */
	private static $_instance = null;

	/*
	 * Get an instance of the Database
	 * @return Instance
	 */
	public static function sharedInstance() {
		if(!self::$_instance) { // If no instance then make one
			self::$_instance = new database();
		}
		return self::$_instance;
	}


	/**
	 * Construction de l'objet
	 *
	 * @internal param $void
	 * @return \database
	 */
	private function __construct()
	{
		$this->dbhost = DB_HOST ?? 'localhost';
        $this->dbname = DB_NAME ?? 'test';
        $this->dbuser = DB_USER ?? 'root';
        $this->dbpwd = DB_PASS ?? '';

        try
        {
            $this->db_connect();
        }
        catch(Exception $e)
        {
            die('Erreur : '.$e->getMessage());
        }



	}


	/**
	 * Connexion à la base de données
	 *
	 * @param void
	 * @return void
	 */
	public function db_connect()
	{


		/** Connexion */

		if ($this->debug) {


			$this->dbh = mysqli_connect( $this->dbhost, $this->dbuser, $this->dbpwd );
			if(mysqli_connect_error()) {
				trigger_error("Failed to connect to MySQL: " . mysqli_connect_error(), E_USER_ERROR);
			}
		} else {


			$this->dbh = @mysqli_connect( $this->dbhost, $this->dbuser, $this->dbpwd  );
		}


		if (!$this->dbh) {

			echo "Etes-vous sur d'avoir correctement paramétré les identifiants de connexion à votre base de données ?";

			die;
		}


		$this->ready = true;


		$this->select( $this->dbname, $this->dbh );


		$this->set_charset( $this->dbh, 'utf8' );
	}


	/**
	 * Sélection de la base de données
	 *
	 * @param string $db le nom de la base de données
	 * @param string $dbh la connexion ouverte
	 */
	public function select( $db, $dbh = null )
	{

		if (is_null( $dbh )) {
			$dbh = $this->dbh;
		}

		if (!@mysqli_select_db($dbh, $db )) {
			echo "Impossible de sélectionner la base de données indiquée.";

			$this->ready = false;
		}

	}


	/**
	 * Modifie le charset de la connexion à la table
	 *
	 * @param string $dbh la connexion ouverte
	 * @param string $charset le type de caractères
	 */
	public function set_charset( $dbh, $charset )
	{
		mysqli_set_charset($dbh, $charset);
	}


	/**
	 * Fermeture de la connexion
	 *
	 * @param void
	 * @return void
	 */
	public function __destruct()
	{

		return true;
	}


	/**
	 * Requête 'select' sur la base de données
	 *
	 * @param string $req la requête à exécuter
	 * @param int|string $return_format le format de sortie (par défaut : tableau)
	 * @param bool $log Ecriture dans le log : true
	 * @return boolean|array
	 */
	public function query( $req, $return_format = 0, $log = false )
	{


		if (empty( $req ) || !$this->ready) {
			return "requete vide";
		}


		/** Exécution de la requête */
		$result = mysqli_query( $this->dbh, $req );


		/** Ecriture de la requête dans le log */
		if ($result === false) {
			new log( 'ERROR', $req );
		} else {
			if ($log) {
				new log( 'DB', $req );
			}
		}


		/** Retourne le résultat en fonction du format demandé */
		if (!$result) {
			return "échec d'enregistrement";
		}


		switch ($return_format) {

			case 1:
				/** Retourne un résultat */
				if (empty( $result )) {
					return "pas de resultat";
				}
				$row = mysqli_fetch_row( $result );
				return $row[0];
				break;

			case 2:
				/** Retourne un tableau dont la clé est le nom du champ de la table */
				$array = array();
				while ($row = mysqli_fetch_assoc( $result )) {
					array_push( $array, $row );
				}
				return $array;
				break;

			case 3:
				/** Retourne une ligne de résultat en tableau */
				return mysqli_fetch_assoc( $result );
				break;

            case 4:
                /** Retourne une ligne de résultat en tableau */
                $row =  $result->num_rows;
                return $row;
                break;

			default:
				/** Retourne le résultat en tableau */
				$array = array();
				while ($row = mysqli_fetch_row( $result )) {
					array_push( $array, $row[0] );
				}
				return $array;
				break;
		}
	}


	/**
	 * Requête exécutive (update, insert, create, ...) sur la base de données
	 *
	 * @param string $req la requête à exécuter
	 * @param bool $log
	 * @return string
	 */
	public function exec( $req, $log = false )
	{


		/** Exécution de la requête */
		$result = @mysqli_query( $this->dbh, $req );


		/** Ecriture de la requête dans le log */
		if ($result === false) {
			new log( 'ERROR', $req );
		} else {
			if ($log) {
				new log( 'DB', $req );
			}
		}

		


		$return = mysqli_affected_rows($this->dbh);

		return ( $return );
	}


	/**
	 * Requête d'insertion sur une table
	 *
	 * @param string $req la requête à exécuter
	 * @param bool $log
	 * @return string $id id du nouvel enregistrement
	 */
	public function insert( $req, $log = false )
	{

		/** Exécution de la requête */
		$result = @mysqli_query($this->dbh, $req );

		$id = mysqli_insert_id($this->dbh);


		/** Ecriture de la requête dans le log */
		if ($result === false) {
			new log( 'ERROR', $req );
		} else {
			if ($log) {
				new log( 'DB', $req );
			}
		}

		return $id;
	}


	/**
	 * Importation d'un fichier sql
	 *
	 * @param string $filename le chemin absolu vers le fichier
	 * @param boolean $mode true pour l'import d'une structure, false pour l'import de données
	 * @return string
	 */
	public function import( $filename, $mode = false )
	{

		clearstatcache();

		/** Si le fichier existe et qu'il est lisible */

		if (file_exists( $filename ) && is_readable( $filename )) {


			/** Récupération du contenu du fichier dans une variable string */
			$string = file_get_contents( $filename, FILE_USE_INCLUDE_PATH );


			/** Définition du délimiteur pour découper les séquences sql */
			if ($mode) {

				$delimiter = ';';
			} else {

				$delimiter = ');';
			}


			/** On met le fichier en tableau en le découpant à chaque délimiteur */
			$array = explode( $delimiter, $string );

			array_pop( $array );

			$nb_query = count( $array );


			/** Boucle sur le tableau récupéré au-dessus */
			foreach ($array as $value) {


				/** Vérifie que la requête n'est pas vide */

				if (!is_null( $value ) || !empty( $value ) || $value != ' ') {


					if ($mode) {

						$result = mysqli_query($this->dbh, $value ); // Exécute la requête
					} else {

						$result = mysqli_query($this->dbh, $value . ")" ); // Ajoute une parenthèse à la fin de la requête et exécution de cette dernière
					}


					/** Recherche du nom de la table dans la requête */

					if ($mode) {

						$needle = "CREATE TABLE `";
					} else {

						$needle = "INSERT INTO `";
					}

					$start = stripos( $value, $needle ) + strlen( $needle );


					$needle = '` (';

					$end = stripos( $value, $needle );


					$length = $end - $start;

					$table_name = substr( $value, $start, $length );


					/** Sortie texte en fonction du résultat obtenu lors de la requête */

					if ($result) {


						if ($mode) { // Si la requête est sur la structure
							$query = "SELECT `CREATE_TIME` FROM `information_schema`.`TABLES` WHERE `TABLE_NAME` = '$table_name'";

							$res = mysqli_query( $this->dbh,$query );

							$sql = mysqli_fetch_array( $res );

							$time = $sql[0];

							// echo "Création de la table <strong>$table_name</strong> réalisée à " . time::format( $time ) . "<br />";

							$return = $nb_query;
						} else { 
							// Si la requête est sur une donnée
							$query = "SELECT COUNT(id) FROM `" . $table_name . "`";

							$res = mysqli_query($this->dbh, $query );

							$sql = mysqli_fetch_array( $res );

							$rows = $sql[0];

							//echo "Insertion de <strong>$rows ligne(s)</strong> dans la table <strong>$table_name</strong>.<br />";

							$return = $nb_query;
						}
					} else { // Si on a une erreur à l'exécution de la requête
						if ($mode) {

							echo "Creation error on table <strong>$table_name</strong>!";
							$return = false;
						} else {

							echo "Data insertion error!";
							$return = false;
						}
					}
				}

			}
			return $return;
		}
	}


	/**
	 * Protection des entrées sur la base de données
	 *
	 * @param string $string la chaine à protéger
	 * @return string
	 */
	public function protect_entry( $string )
	{
		return ( mysqli_real_escape_string( $this->dbh, $string ) );
	}


	/**
	 * Retourne un objet d'après un tableau en utilisant les clés comme propriétés de l'objet
	 *
	 * @param $object /objet déclaré
	 * @param $array /tableau à transformer
	 */
	public static function getObject( $object, $array )
	{

		foreach (reset( $array ) as $key => $value) {
			$object->$key = $value;
		}
	}

}