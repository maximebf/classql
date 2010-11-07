
# Syntax

## Variables



## Arrays

Arrays are defined between square brackets. 

    [ "value1", "value2" ]
    [ 0 => "value1", 1 => "value2" ]
    [ key => "value1", key2 => "value2" ]

Arrays can be used the same way as in php, as when defining them, no need to use string for keys.

    create_user($data) {
        INSERT INTO users (username, password) VALUES ($data[username], $data[password])
    }
