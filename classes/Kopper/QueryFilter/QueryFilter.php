<?php

namespace Kopper\QueryFilter;

use Kopper\Utility;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Util\PropelModelPager;

class QueryFilter {

  const PAGE = 'page';
  const COMPARISON_SUFFIX = '-cp';

  public function getPage(array $params) {
    return isset($params[self::PAGE]) ? $params[self::PAGE] : 1;
  }

  protected function clean($key) {
    return str_replace(array('-min', '-max'), '', $key);
  }

  public function getRealComparison($comparison) {
    switch (strtolower($comparison)) {
      case 'like':
        $comparison = Criteria::LIKE;
        break;
      case 'not like':
        $comparison = Criteria::NOT_LIKE;
        break;
      case 'in':
        $comparison = Criteria::IN;
        break;
      case 'not in':
        $comparison = Criteria::NOT_IN;
        break;
    }

    return $comparison;
  }

  public function addFilters(ModelCriteria $query, array $filters) {
    foreach ($filters as $key => $value) {
      if ($key === 'q' && is_callable(array($query, 'filterByFullText')) === true) {
        $query->filterByFullText($value);
      } else if (Utility::inString('orderBy', $key) === true) {
        $method = $key;

        if (is_callable(array($query, $method)) === true) {
          $query = call_user_func(array($query, $method), $value);
        }
      } else if (Utility::inString(self::COMPARISON_SUFFIX, $key) === false) {
        $cleanKey = $this->clean($key);
        if ($query->getTableMap()->hasColumnByPhpName(ucfirst($cleanKey)) === true) {
          $method = 'filterBy' . $cleanKey;

          $comparison = Criteria::EQUAL;

          if (empty($filters[$key . self::COMPARISON_SUFFIX]) === false) {
            $comparison = $this->getRealComparison($filters[$key . self::COMPARISON_SUFFIX]);
          }

          if (is_callable(array($query, $method)) === true) {
            $query = call_user_func(array($query, $method), $value, $comparison);
          }
        }
      }
    }

    return $query;
  }

  public function paginate(ModelCriteria $query, $page = 1, $maxPerPage = 25) {
    return $query->paginate($page, $maxPerPage);
  }

  public function getFirstPage(PropelModelPager $pager) {
    return $pager->getFirstPage();
  }

  public function getPrevPage(PropelModelPager $pager) {
    if ($pager->haveToPaginate() === true && $pager->getPage() > $pager->getFirstPage()) {
      return $pager->getPreviousPage();
    }

    return null;
  }

  public function getNextPage(PropelModelPager $pager) {
    if ($pager->haveToPaginate() === true && $pager->getPage() < $pager->getLastPage()) {
      return $pager->getNextPage();
    }

    return null;
  }

  public function getLastPage(PropelModelPager $pager) {
    return $pager->getLastPage();
  }

  public function getLinks(PropelModelPager $pager) {
    $links = array(
      'first' => $this->getFirstPage($pager),
      'prev' => $this->getPrevPage($pager),
      'next' => $this->getNextPage($pager),
      'last' => $this->getLastPage($pager),
    );

    $uri = (empty($_SERVER['SERVER_NAME']) ? '' : (Utility::getProtocol() . '://' . $_SERVER['SERVER_NAME'])) . (empty($_SERVER['REQUEST_URI']) ? null : $_SERVER['REQUEST_URI']);

    foreach ($links as &$link) {
      if (empty($link) === false) {
        if (Utility::inString(self::PAGE, $uri) === true) {
          $link = preg_replace('/' . self::PAGE . '=\d+/', self::PAGE . '=' . $link, $uri);
        } else {
          $operator = Utility::inString('?', $uri) ? '&' : '?';
          $link = $uri . $operator . self::PAGE . '=' . $link;
        }
      }
    }
    
    return $links;
  }

}
