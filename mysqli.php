<?php

class NewDatabaseConnection {
  protected $mysqli = '';
  protected $table  = '';
  public $message   = '';

  public function __construct($host, $db, $user, $pass, $table = 'customer') {
    $charset = 'utf8';
    $this->table = $table;

    $this->mysqli = new mysqli($host, $user, $pass, $db);
    if(!$this->mysqli) {
      echo "<br>Error! ".$this->mysqli->connect_error()."<br>";
      die("Write correct data.");
    }
  }

  public function main ($table = 'test') {
    //создаем таблицу category и устанавливаем имена полей
    $fields = array('category', 'product_id', 'discount');
    $this->createTable('category', $fields);

    $this->insert(array(array('Birthday Discount', 1, 20)));

    //создаем таблицу customer и устанавливаем имена полей
    $fields = array('name', 'surname', 'city');
    $this->createTable('customer', $fields);

    $this->insert(array(array(Igor, Vasyliev, Odessa), array(Pavel, Ivanov, Kiev), array(Petr, Ignatiev, Lvov)));

    //создаем таблицу order и устанавливаем имена полей
    $fields = array('customer_id', 'product_id', 'comment');
    $this->createTable('order', $fields);

    $this->insert(array(array(1, 2, "And take him + 5%"), array(2, 1, "Discount card")));

    //создаем таблицу product и устанавливаем имена полей
    $fields = array('name', 'price', 'warranty');
    $this->createTable('product', $fields);

    $this->insert(array(array(Igor, 100, 1), array(Pavel, 200, 2)));

    //выводим из таблицы customer для проверки ввода данных
    echo "<br>Test selection<br>".PHP_EOL;
    $this->useTable('customer');
    $this->select("name, surname");
    $this->pdo = NULL;
  }

  public function useTable($table) {
    $this->table = $table;
  }

  public function createTable ($table, $params) {
    $sqlstart = "CREATE TABLE IF NOT EXISTS `{$table}` (id INT (11) AUTO_INCREMENT,";
    $sqlend   = "PRIMARY KEY (id))";

    foreach ($params as $key => $value) {
      $sqlstart .= "{$value} VARCHAR (20),";
    }
    $sqlstart .= $sqlend;

    $sql = $this->mysqli->prepare($sqlstart);
    $sql->execute();

    $this->table = $table;
    $this->message = 'Table '.$table.' was created.<br>'.PHP_EOL;
    echo $this->message;
  }

  public function insert($array) {
    if (empty($array)) die ('It\'s not correct data');

    try {
      echo "Start insert".PHP_EOL;
      $this->transactionStarted();
      $query = $this->mysqli->stmt_init();
      $this->mysqli->begin_transaction();

      foreach($array as $value) {
        $tmpl = "INSERT INTO `{$this->table}` VALUES (NULL, ?, ?, ?)";
        $query->prepare($tmpl);
        $query->bind_param('sss', $value[0], $value[1], $value[2]);
        $query->execute();
      }

      $this->mysqli->commit();
      $this->transactionSucsessEnd();
    }
    catch (Exception $e) {
      $this->mysqli->rollback();
      $this->transactionWasNotSucsess();
    }
  }

  public function updateById ($param, $val, $array) {
    if (empty($array)) die ('It\'s not correct data');

    try {
      echo "Start updating".PHP_EOL;
      $this->transactionStarted();
      $query = $this->mysqli->stmt_init();
      $this->mysqli->begin_transaction();

      foreach ($array as $key => $value) {
        $tmpl = "UPDATE `{$this->table}` SET $param = ? WHERE id = ?";
        $query->prepare($tmpl);
        $query->bindValue(1, $val, $array[$key]);
        $query->execute();
      }

      $this->mysqli->commit();
      $this->transactionSucsessEnd();
    }
    catch(Exception $e) {
      $this->mysqli->rollback();
      $this->transactionWasNotSucsess();
    }
  }

  public function deleteById($id) {
    if (empty($id)) die ('It\'s not correct data');

    try {
      echo "Start delete string.".PHP_EOL;
      $this->transactionStarted();
      $query = $this->mysqli->stmt_init();
      $this->mysqli->beginTransaction();

      $tmpl = "DELETE FROM `{$this->table}` WHERE id = ?";
      $query = $this->mysqli->prepare($tmpl);
      $query->bindValue('s', $id);
      $query->execute();

      $this->mysqli->commit();
      $this->transactionSucsessEnd();
    }
    catch(Exception $e) {
      $this->mysqli->rollback();
      $this->transactionWasNotSucsess();
    }
  }

  public function select($array, $table = 'customer') {
    echo "<br>Start selection.".PHP_EOL;
    $this->table = $table;
    $query = $this->mysqli->query("SELECT {$array} FROM `{$this->table}`");

    echo $query->queryString;

    $result = explode(", ", $array);

    echo "<br><table>";
    foreach ($result as $value) {
      echo "<tr><td>".$value."<td>";
      foreach ($this->mysqli->query("SELECT {$array} FROM {$this->table}") as $row) {
        print "<td>".$row[$value] . "<td>";
      }
      echo "</tr>";
    }
    echo "</table>";
  }

  public function transactionStarted() {
    echo "Start transaction.".PHP_EOL;
  }

  public function transactionSucsessEnd() {
    echo "End transaction. Sucsess.<br>".PHP_EOL;
  }

  public function transactionWasNotSucsess() {
    echo "Fail transaction. Rollback!<br>".PHP_EOL;
  }
}
//настройки подключения
$host = 'localhost';
$database = 'data';
$user = 'root';
$pass = 'root';

//подключаемся к базе
$query = new NewDatabaseConnection($host, $database, $user, $pass);
/*
  Если хотим закинуть тестовую базу - запускаем этот метод.
  Можем отдельно создавать что-то своё.
*/
$query->main();
