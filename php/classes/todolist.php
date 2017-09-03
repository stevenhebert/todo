<?php

namespace Edu\Cnm\todolist;

require_once("autoload.php");

/**
 * Todo Class
 *
 * This class stores the todo list entry items
 * It creates the todo table
 * It inserts the todo object
 * It updates the todo object
 * It deletes the todo object
 * It gets by object name
 *
 * @author Steven Hebert <hebertsteven@me.com>
 *
 **/


class todo {


	/**
	 * id for todo item
	 * this is the primary key
	 * @var int $id
	 *
	 **/
	private $id;

	/**
	 * title for this todo item
	 * @var string $title
	 *
	 */
	private $title;

	/**
	 * description for todo item
	 * @var string $description
	 *
	 */
	private $description;

	/**
	 * date and time task was posted
	 * @var \DateTime $taskDate
	 *
	 */
	private $taskDate;


	/**
	 * constructor for todo class
	 *
	 * @param int $newId
	 * @param string $newTitle
	 * @param string $newDescription
	 *
	 * @throws \InvalidArgumentException if data types are not valid
	 * @throws \RangeException if data values are out of bounds (e.g., strings too long, negative integers)
	 * @throws \TypeError if data types violate type hints
	 * @throws \Exception if some other exception occurs
	 *
	 * @Documentation https://php.net/manual/en/language.oop5.decon.php
	 *
	 */
	public function __construct(?int $newId, string $newTitle, string $newDescription) {
		try {
			$this->setId($newId);
			$this->setTitle($newTitle);
			$this->setDescription($newDescription);
		} //determine what exception was thrown
		catch(\InvalidArgumentException | \RangeException | \Exception | \TypeError $exception) {
			$exceptionType = get_class($exception);
			throw(new $exceptionType($exception->getMessage(), 0, $exception));
		}
	}

	/**
	 * accessor for id
	 *
	 * @return int | null value of id
	 *
	 **/
	public function getId(): int {
		return ($this->id);
	}

	/**
	 * mutator for id
	 *
	 * @param int | null $id
	 * @throws \RangeException if $newId is not positive
	 * @throws \TypeError if $newId is not an integer
	 *
	 */
	public function setId(? $newId): void {
		if($newId === null) {
			$this->Id = null;
			return;
		}

		//verify the id is positive
		if($newId <= 0) {
			throw(new \RangeException("id must be positive"));
		}

		//convert and store the id
		$this->Id = $newId;
	}


	/**
	 * accessor for title
	 *
	 * @return string value of title
	 *
	 **/
	public function getTitle(): string {
		return ($this->title);
	}

	/**
	 * mutator for title
	 *
	 * @param string $newTitle
	 *
	 */
	public function setTitle(string $newTitle): void {
		//verfiy the title is secure
		$newTitle = trim($newTitle);
		$newTitle = filter_var($newTitle, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($newTitle) === true) {
			throw(new \InvalidArgumentException("title cannot be empty"));
		}

		//verify the title will fit in the database
		if(strlen($newTitle) > 32) {
			throw(new \RangeException("title is too long"));
		}

		//store the title
		$this->title = $newTitle;
	}

	/**
	 * accessor for description
	 *
	 * @return string value of description
	 *
	 **/
	public function getDescription(): string {
		return ($this->description);
	}

	/**
	 * mutator for description
	 *
	 * @param string $description
	 *
	 */
	public function setDescription(string $newDescription): void {
		//verify the description is secure
		$newDescription = trim($newDescription);
		$newDescription = filter_var($newDescription, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($newDescription) === true) {
			throw(new \InvalidArgumentException("description cannot be empty"));
		}
		//verify the description will fit in the database
		if(strlen($newDescription) > 8192) {
			throw(new \RangeException("description is too long"));
		}

		//store the description
		$this->description = $newDescription;
	}

	/**
	 * accessor method for taskDate
	 *
	 * @return \DateTime value date and time task was posted
	 **/
	public function getTaskDate(): \DateTime {
		return ($this->taskDate);
	}

	/**
	 * mutator method for task date and time
	 *
	 * @param \DateTime | string | null $newtaskDate where the date the task was submitted is a DateTime object, string, or null (if submitting)
	 * @throws \InvalidArgumentException if $newTaskDate is not a valid object or string
	 * @throws \RangeException if $newTaskDate is a date or time that does not exist
	 *
	 **/
	public function setTaskDate($newTaskDate = null): void {
		//base case: if the date is null, wait for mySQL
		if($newTaskDate === null) {
			$this->taskDate = null;
			return;
		}

		//store the task date and time using ValidateDate trait
		try {
			$newTaskDate = self::validateDateTime($newTaskDate);
		} catch(\InvalidArgumentException | \RangeException $exception) {
			$exceptionType = get_class($exception);
			throw(new $exceptionType($exception->getMessage(), 0, $exception));
		}
		$this->taskDate = $newTaskDate;
	}

	/**
	 * INSERTS object into mySQL
	 *
	 * @param \PDO $pdo connection object
	 *
	 * @throws \PDO exception when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 *
	 */
	public function insert(\PDO $pdo): void {
		//enforce the id is null, don't want to overwrite an item that already exists (update)
		if($this->id !== null) {
			throw(new \PDOException("not a new task"));
		}

		//create a new query template
		$query = "INSERT INTO todo(id, title, description) VALUES (:id, :title, :description)";
		$statement = $pdo->prepare($query);

		//bind the member variables to the place holders in the template
		$parameters = ["id" => $this->id, "title" => $this->title, "description" => $this->description];
		$statement->execute($parameters);

		//update the null id with what mySQL gives us
		$this->id = intval($pdo->lastInsertId());

		//update the auto generated timestamp
		$tempTask = todo::getTaskById($pdo, $this->id);

		$this->setTaskDate($tempTask->getTaskDate);
	}

	/**
	 * DELETE a task in mySQL
	 *
	 * @param \PDO $pdo PDO connection object
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function delete(\PDO $pdo): void {
		//enforce the id is not null; can't delete something that doesn't exist
		if($this->id === null) {
			throw(new \PDOException("unable to delete the task because it doesn't exist"));
		}

		//create a query template
		$query = "DELETE FROM todo WHERE id = :id";
		$statement = $pdo->prepare($query);

		//bind the member variables to the place holder in the template
		$parameters = ["id" => $this->id];
		$statement->execute($parameters);
	}

	/**
	 * UPDATE a task in mySQL
	 *
	 * @param \PDO $pdo PDO connection object
	 * @throws \PDOException for mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function update(\PDO $pdo): void {
		//enforce that id is not null; can't update something that doesn't exist
		if($this->id === null) {
			throw(new \PDOException("unable to update the task because it does not exist"));
		}

		//create the query template
		$query = "UPDATE todo SET title = :title, description = :description WHERE id = :id";
		$statement = $pdo->prepare($query);

		//bind the member variables to the placeholders in the template
		$parameters = ["id" => $this->id, "title" => $this->title, "description" => $this->description];
		$statement->execute($parameters);

		//update the auto generated timestamp
		$tempTask = todo::getById($pdo, $this->id);

		$this->setTaskDate($tempTask->getTaskDate);
	}

	/**
	 * get a task by id
	 *
	 * @param \PDO $pdo connection object
	 * @param int $id to search for
	 * @return task | null if was task found or null if not found
	 * @throws \PDOException for mySQL related error
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getTaskById(\PDO $pdo, int $id): ?Task {

		//sanitize the id before searching
		if($id <= 0) {
			throw(new \PDOException("id is not positive"));
		}

		//create query template
		$query = "SELECT id, title, description, taskDate FROM todo WHERE id = :id";
		$statement = $pdo->prepare($query);

		//bind the id to the placeholder in the template
		$parameters = ["id" => $id];
		$statement->execute($parameters);

		//grab task from mySQL
		try {
			$task = null;
			$statement->setFetchMode(\PDO::FETCH_ASSOC);
			$row = $statement->fetch();
			if($row !== false) {
				$task = new Task($row["id"], $row["title"], $row["description"], $row["taskDate"]);
			}
		} catch(\Exception $exception) {
			//if the row couldn't be converted, rethrow it
			throw(new \PDOException($exception->getMessage(), 0, $exception));
		}
		return ($task);
	}

	/** gets tasks by title
	 *
	 * @param \PDO $pdo PDO connection object
	 * @param string $postContent post content to search for
	 * @return \SplFixedArray SPLFixedArray of posts found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getTaskByTitle(\PDO $pdo, string $title): \SplFixedArray {
		//sanitize the description before searching
		$title = trim($title);
		$title = filter_var($title, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($title) === true) {
			throw(new \PDOException("title is invalid"));
		}

		//escape any mySQL wild cards
		$title = str_replace("_", "\\_", str_replace("%", "\\%", $title));

		//create query template
		$query = "SELECT id, title, description, taskDate FROM todo WHERE title = :title";
		$statement = $pdo->prepare($query);

		//bind the title to the placeholder in the template
		$title = "%$title";
		$parameters = ["title" => $title];
		$statement->execute($parameters);

		//build an array of posts
		$tasks = new \SplFixedArray($statement->rowCount());
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$task = new Task($row["id"], $row["title"], $row["description"], $row["taskDate"]);
				$tasks[$tasks->key()] = $task;
				$tasks->next();
			} catch(\Exception $exception) {
				//if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($tasks);
	}


	/** get an array of tasks by date
	 *
	 * @param \PDO $pdo connection object
	 * @param \DateTime $sunriseDate beginning date of search
	 * @param \DateTime $sunsetDate ending date of search
	 * @return \SplFixedArray of tasks found
	 * @throws \PDOException error when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 * @throws \InvalidArgumentException if either sun dates are in the wrong format
	 **/
	public static function getTaskByDate(\PDO $pdo, $sunriseDate, $sunsetDate): \SplFixedArray {
		//enforce both dates are present
		if((empty($sunriseDate) === true) || (empty($sunsetDate) === true)) {
			throw(new \InvalidArgumentException("dates are empty or insecure"));
		}

		//ensure both dates are in the correct format and are secure
		try {
			$sunriseDate = self::validateDateTime($sunriseDate);
			$sunsetDate = self::validateDateTime($sunsetDate);
		} catch(\InvalidArgumentException | \RangeException $exception) {
			$exceptionType = get_class($exception);
			throw(new $exceptionType($exception->getMessage(), 0, $exception));
		}

		//create query template
		$query = "SELECT id, title, description, taskDate FROM todo WHERE taskDate >= :sunriseDate AND taskDate <= :sunsetDate";
		$statement = $pdo->prepare($query);

		//format the dates so that mySQL can use them
		$formattedSunriseDate = $sunriseDate->format("Y-m-d H:i:s.u");
		$formattedSunsetDate = $sunsetDate->format("Y-m-d H:i:s.u");


		$parameters = ["sunriseDate" => $formattedSunriseDate, "sunsetDate" => $formattedSunsetDate];
		$statement->execute($parameters);

		//build an array of posts
		$tasks = new \SplFixedArray($statement->rowCount());
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$task = new Task($row["id"], $row["title"], $row["description"], $row["taskDate"]);
				$tasks[$tasks->key()] = $task;
				$tasks->next();
			} catch(\Exception $exception) {
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($tasks);
	}

	/**
	 * get all tasks
	 *
	 * @param \PDO $pdo PDO connection object
	 * @return \SplFixedArray SplFixedArray of posts found or null if not found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getAllTasks(\PDO $pdo): \SplFixedArray {
		//create query template
		$query = "SELECT id, title, description, taskDate FROM todo";

		$statement = $pdo->prepare($query);
		$statement->execute();

		//build an array of tasks
		$tasks = new \SplFixedArray($statement->rowCount());
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$task = new Task($row["id"], $row["title"], $row["description"], $row["taskDate"]);
				$task[$tasks->key()] = $task;
				$tasks->next();
			} catch(\Exception $exception) {
				//if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($tasks);
	}

	/*
	* needed to add the microsecond to the taskDate field

	* @param $fields object to process taskDate
	*
	*/
	public function jsonSerialize() {
		$fields = get_object_vars($this);
		//format the data so that the front end can consume it
		$fields["taskDate"] = round(floatval($this->taskDate->format("U.u")) * 1000);
		return ($fields);
	}

}