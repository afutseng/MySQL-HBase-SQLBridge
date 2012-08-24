<?php
namespace MySQLMigrationBridge;

class PredicateException extends \Exception
{
}

class Predicate {
  protected static $predicates;
  protected static $predicates_operators;

  public static function parseMultiPredicate($expression) {
    //preg_match_all("/\s*([\w`]+)\s+([<=>LIKE]+)\s+([\w\W_@.!#$\%&'*`]+)\s*([ANDOR]+)?\s+/i", $expression, $matches);
    preg_match_all("/\s*([\w`]+)\s+([<=>LIKE]+)\s+([\x{4e00}-\x{9fa5}A-Za-z0-9_'%]+)\s*([ANDORandor]+)?\s+/ui", $expression, $matches);
    //echo "=====start matchMultiPredicate=====<br>\n";

    if (isset($matches[0][0])) {
      $predicate_max_cnt = count($matches[1]);
      $idx = 0;
      $predicates = array();

      // 開始蒐集 predicate, 最多 $predicate_max_cnt 句
      for ($idx = 0; $idx < $predicate_max_cnt; ++$idx) {
        $predicates[$idx] = "";

        // v1 . op . v2
        for ($i = 1; $i <= 3; ++$i) {
          if (! isset($matches[$i][$idx])) {
            break;
          }
          $predicates[$idx] .= $matches[$i][$idx] . " ";
        }

        // 砍掉不具備兩個運算元與一個運算子的predicate
        if ($i != 4) {
          unset($predicates[$idx]);
        }
      }

    }


    self::$predicates = $predicates;
    // collect operators if has any operators
    if (isset($matches[4])) {
      $matches[4] = array_map("strtoupper", $matches[4]);
      self::$predicates_operators = array_filter($matches[4]);
    }

    return $predicates;
  }

  public static function getPredicates() {
    return self::$predicates;
  }

  public static function getOperators() {
    return self::$predicates_operators;
  }

  public static function getMappingPredicate($expression) {
    preg_match_all("/\s*([\w`]+)\s*([<|=|>]+)\s*([\x{4e00}-\x{9fa5}A-Za-z0-9_'%`]+)\s*([ANDORandor]+)?\s+/ui", $expression, $matches);
    for ($i = 1, $cnt = count($matches); $i < $cnt; ++$i) {
      if (isset($matches[$i][0])) {
        $tokens[] = $matches[$i][0];
      }
    }

    $expression = preg_replace("/\s+/", " ", $expression);
    if (preg_match("/ NOT LIKE /i", $expression)) {
      list($qualifier, $not, $op, $value) = explode(" ", $expression);
      return new LikePredicate($qualifier, "$not $op", str_replace("'", "", $value));
    }
    elseif (preg_match("/ LIKE /i", $expression)) {
      list($qualifier, $op, $value) = explode(" ", $expression);
      return new LikePredicate($qualifier, $op, str_replace("'", "", $value));
    }
    elseif (preg_match("/ [<|=|>]+ /i", $expression)) {
      list($qualifier, $op, $value) = $tokens;
      return new ComparisonPredicate($qualifier, $op, str_replace("'", "", $value));
    }
    else {
      return false;
    }
  }
}

/**
 * 
 * 
<comparison predicate> ::=
    <row value constructor> <comp op>
        <row value constructor>
*/
class ComparisonPredicate extends Predicate {
  private $row_value_constructor1;
  private $comp_op;
  private $row_value_constructor2;

  public function __construct($rvc1, $op, $rvc2) {
    $this->row_value_constructor1 = $rvc1;
    $this->comp_op = $op;
    $this->row_value_constructor2 = $rvc2;
  }

  public function toFilterArguments() {
    $o = new \stdClass;
    $o->qualifier = $this->row_value_constructor1;
    $o->compareOperator = $this->comp_op;
    $o->comparatorType = "binary";
    $o->comparatorValue = $this->row_value_constructor2;
    $o->comparator = "binary:{$this->row_value_constructor2}";
    return $o;
  }
}

class LikePredicate extends Predicate {
  //
  private $match_value;
  private $like_str;
  private $pattern;

  public function __construct($match_value, $like_str, $pattern) {
    $this->match_value = $match_value;
    $this->like_str = $like_str;
    $this->pattern = $pattern;
  }

  public function toFilterArguments() {

    $value = $this->pattern;
    // %value%
    if (preg_match("/^%([\w\W]+)%$/", $value)) {
      $value = str_replace("%", "", $value);
    }
    // value%
    elseif (preg_match("/%$/", $value)) {
      $value = str_replace("%", "", $value);
      //$comparator = "substring";
      $value = "^" . $value;
    }
    // %value
    elseif (preg_match("/^%/", $value)) {
      $value = str_replace("%", "", $value);
      //$comparator = "substring";
      $value .= "$";
    }
    else {
      $value = "^".$value."$";  
    }

    $o = new \stdClass;
    $o->qualifier = $this->match_value;
    $o->compareOperator = ($this->like_str == "NOT LIKE") ? "!=" : "=";
    $o->comparatorType = "regexstring";
    $o->comparatorValue = $value;
    $o->comparator = "{$o->comparatorType}:{$value}";

    return $o;
  }
}