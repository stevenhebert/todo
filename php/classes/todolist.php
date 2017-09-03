<?php

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
	 * @var int id
	 *
	 **/
	private $id;

	/**
	 * title for this todo item
	 * @var string title
	 *
	 */
	private $title;

	/**
	 * description for todo item
	 * @var string description
	 *
	 */
	private $description;

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
		return ($this->id)
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

}