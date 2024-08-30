-- File: employee_search_procedure.sql
DELIMITER //

CREATE PROCEDURE get_employees_by_department(IN dept_name VARCHAR(50))
BEGIN
    SELECT employee_id, first_name, last_name, hire_date
    FROM employees e
    JOIN departments d ON e.department_id = d.department_id
    WHERE d.department_name = dept_name;
END //

DELIMITER ;