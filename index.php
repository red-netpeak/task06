<?php

class NewDatabaseConnection {
  protected $pdo   = '';
  protected $table = '';
  public $message  = '';

  public function __construct($host, $db, $user, $pass, $table = 'test') {
    $charset = 'utf8';
    $this->table = $table;
    try {
      $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

      $opt = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      );

      $this->pdo = new PDO($dsn, $user, $pass, $opt);
    }
    catch(Exception $e) {
      echo "<br>Error! ".$e->getMessage()."<br>";
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

    $sql = $this->pdo->prepare($sqlstart);
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
      $this->pdo->beginTransaction();

      foreach($array as $value) {
        $tmpl = "INSERT INTO `{$this->table}` VALUES (NULL, :value1, :value2, :value3)";
        $query = $this->pdo->prepare($tmpl);
        $query->bindValue('value1', $value[0]);
        $query->bindValue('value2', $value[1]);
        $query->bindValue('value3', $value[2]);
        $query->execute();
      }

      $this->pdo->commit();
      $this->transactionSucsessEnd();
    }
    catch (Exception $e) {
      $this->pdo->rollback();
      $this->transactionWasNotSucsess();
    }
  }

  public function updateById ($param, $val, $array) {
    if (empty($array)) die ('It\'s not correct data');

    try {
      echo "Start updating".PHP_EOL;
      $this->transactionStarted();
      $this->pdo->beginTransaction();

      foreach ($array as $key => $value) {
        $tmpl = "UPDATE `{$this->table}` SET $param = ? WHERE id = ?";
        $query = $this->pdo->prepare($tmpl);
        $query->bindValue(1, $val);
        $query->bindValue(2, $array[$key]);
        $query->execute();
      }

      $this->pdo->commit();
      $this->transactionSucsessEnd();
    }
    catch(Exception $e) {
      $this->pdo->rollback();
      $this->transactionWasNotSucsess();
    }
  }

  public function deleteById($id) {
    if (empty($id)) die ('It\'s not correct data');

    try {
      echo "Start delete string.".PHP_EOL;
      $this->transactionStarted();
      $this->pdo->beginTransaction();

      $tmpl = "DELETE FROM `{$this->table}` WHERE id = ?";
      $query = $this->pdo->prepare($tmpl);
      $query->bindValue(1, $id);
      $query->execute();

      $this->pdo->commit();
      $this->transactionSucsessEnd();
    }
    catch(Exception $e) {
      $this->pdo->rollback();
      $this->transactionWasNotSucsess();
    }
  }

  public function select($array, $table = 'customer') {
    echo "<br>Start selection.".PHP_EOL;
    $this->table = $table;
    $query = $this->pdo->query("SELECT {$array} FROM `{$this->table}`");

    echo $query->queryString;

    $result = explode(", ", $array);

    echo "<br><table>";
    foreach ($result as $value) {
      echo "<tr><td>".$value."<td>";
      foreach ($this->pdo->query("SELECT {$array} FROM {$this->table}") as $row) {
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
