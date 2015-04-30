<?php
namespace killerCart;

/**
 * Report class
 *
 * Report methods
 *
 * PHP version 5
 *
 * @category  killerCart
 * @package   killerCart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @version   v0.0.1
 */
class Report
{
    /**
     * Database connection
     * Database query sql
     * Database query data
     *
     * @var PDO $DB
     * @var string $sql
     * @var string $sqlData
     */
    protected $DB, $sql, $sqlData;

    /**
     * Class data
     *
     * @var array $data
     */
    protected $data = array();

    /**
     * Main magic methods
     */
    public function __construct()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->DB = new \Database();
    }

    /**
     * getSalesReportByStatus
     * 
     * Returns report of sales totals for selected cart(s) for date range and status codes if provided
     * 
     * @param int|string $cartID Cart ID or ALL for all
     * @param string Datetime to limit query to
     * @param string|array Status Code(s) to include in report
     * 
     * @return array Report details
     */
    public function getSalesReportByStatus($cartID, $dateFrom, $statusCodes)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Cart ID
        if (strtoupper($cartID) == 'ALL') {
            $whereSql = "IS NOT NULL";
        } else {
            $whereSql = "= " . $cartID;
        }
        // Status Codes
        if (is_array($statusCodes)) {
            $statusCodes = implode("', '", $statusCodes);
        }
        $this->sql = "
            SELECT
                co.dateOrderPlaced, co.totalOrderPrice, co.totalOrderTax, co.totalOrderDelivery, co.orderTaxableAmount,
                cop.productID, cop.quantity
            FROM
                customerOrders AS co
                INNER JOIN customers AS c
                    ON co.customerID = c.customerID
                INNER JOIN customerOrderProducts AS cop
                    ON co.orderID = cop.orderID
            WHERE
                c.cartID " . $whereSql . "
                AND co.orderStatusCode IN ('" . $statusCodes . "')
                AND co.dateOrderPlaced > '" . $dateFrom . "'";
        $res       = $this->DB->query($this->sql);
        return $res;
    }

    public function getSalesReportGroupByItem($cartID, $dateFrom, $statusCodes, $sortByPrice = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Cart ID
        if (strtoupper($cartID) == 'ALL') {
            $whereSql = "IS NOT NULL";
        } else {
            $whereSql = "= " . $cartID;
        }
        // Status Codes
        if (is_array($statusCodes)) {
            $statusCodes = implode("', '", $statusCodes);
        }
        // Sort
        if (!empty($sortByPrice)) {
            $sortSql = 'quantity';
        } else {
            $sortSql = 'productTotal';
        }
        $this->sql = "
            SELECT
                cop.productID, SUM(cop.quantity) AS quantity,
                (SELECT p.price * SUM(cop.quantity)) AS productTotal, p.productName
            FROM
                customerOrders AS co
                INNER JOIN customers AS c
                    ON co.customerID = c.customerID
                INNER JOIN customerOrderProducts AS cop
                    ON co.orderID = cop.orderID
                INNER JOIN products AS p
                    ON cop.productID = p.productID
            WHERE
                c.cartID IS NOT NULL
                AND co.orderStatusCode IN ('" . $statusCodes . "')
                AND co.dateOrderPlaced > '" . $dateFrom . "'

            GROUP BY cop.productID

            ORDER BY " . $sortSql . " DESC";
        $res       = $this->DB->query($this->sql);
        \Errors::debugLogger(__METHOD__ . $this->sql, 9);
        return $res;
    }

}