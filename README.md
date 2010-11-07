# ClassQL

THIS IS A WORK IN PROGRESS AND IN A STATE OF EXPERIMENTATION

ClassQL is a new kind of ORM for PHP 5.3. Based on a custom syntax that wraps around SQL code, it allows
to create "object oriented" SQL. No PHP is involved in your models: you define them in SQL!

## Features

*  Easy to learn syntax
*  Your own sql queries
*  Custom filters to compute sql results
*  Supports for eager loading (todo)
*  Supports some kind of inheritance
*  Complete PHP API
*  PHP stream wrapper to include models as php classes
*  Caching of compiled models

## Example

### Defining models

See a detailed example in the demo folder

    User {
        
        // columns
        id int;
        email text;
        password text;
        firstName text;
        lastName text;
        
        // vars
        $fullName = firstName || ' ' || lastName;
        $SELECT = SELECT id, email, firstName, lastName, $fullName;
        
        // by default, methods returns a collection of their defining class
        // the keyword $this will be replaced by the table name
        static findAll() {
            $SELECT FROM $this
        }

        // this methods returns a single User object
        static findById($id) : self {
            $SELECT FROM $this WHERE id = $id
        }
        
        static count() : value {
            SELECT COUNT(*) FROM $this
        }
        
        // this method forwards the call to another one
        findMessages() -> Message::findAllByUserId($id)
    }

    Message {

        id int;
        user_id int references users(id);
        message text;

        static findAllByUserId($id) {
            SELECT * FROM $this WHERE user_id = $id
        }
    }
    
### Usage

    $user = User::findById(1);
    echo $user->email;
    $messages = $user->findMessages();
    

