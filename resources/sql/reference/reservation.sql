select * from items i inner join (
  select * from reservations r
    inner join reservation_items ri
    on r.id = ri.reservation_id where user_id = 3) jr
  on i.id = jr.item_id order by borrowing_start_date asc;

----------------------------------------------------------

SELECT 
    jr.*,
    i.name
FROM items i
INNER JOIN (
    SELECT 
        r.id AS reservation_id,
        ri.item_id,
        r.borrowing_start_date
    FROM reservations r
    INNER JOIN reservation_items ri
    ON r.id = ri.reservation_id
    WHERE r.user_id = 3
) jr
ON i.id = jr.item_id
ORDER BY jr.reservation_id ASC;