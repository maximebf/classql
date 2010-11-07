
# Classes

Classes allowed you to define objects that will be mapped to the class methods results.
The most common case is to map a table. Methods can be static and several modifiers cab
be used (public, private or protected).  

    User {
        static findAll() {
            SELECT username, email FROM users
        }
        
        insert() {
            INSERT INTO users (username, password, email)
            VALUES ($username, $password, $email)
        }
    }
    
    // PHP:
    
    $users = User::findAll();
    foreach ($users as $user) {
        echo $user->username;
    }
    
    $user = new User();
    $user->username = 'foo';
    $user->email = 'foo@example.com';
    $user->password = 'bar';
    $user->insert();

The $this variable references the current table name. By default, the table name is the
same as the class name. This can be done using the _represents_ keyword in the class
definition.

    User represents users {
        static findAll() {
            SELECT username, email FROM $this
        }
    }

Class variables can be defined in the class body.

    User {
        $SELECT = SELECT username, email;
        $roles = [ 0 => 'guest', 1 => 'admin' ];
        
        static findAll() {
            $SELECT FROM $this
        }
    }

Table's columns can also be defined in the class body.

    User {
        id int primary key auto_increment;
        username varchar(100);
        password varchar(100);
        
        // ...
    }
