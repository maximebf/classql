
# Functions

Functions are pretty much defined the same way as with PHP but no need for the _function_ keyword. 
The function's body can be any SQL code. Parameters variables can be used inside the SQL.

    find_all_users() {
        SELECT * FROM users
    }
    
    find_all_user($id) {
        SELECT * FROM users WHERE id = $id
    }

Parameters can have default value.

    find_all_users($limit = 20) {
        SELECT * FROM users LIMIT $limit
    }


