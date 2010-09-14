# PSQL (A better name as yet to be found!)

THIS IS A WORK IN PROGRESS AND IN A STATE OF EXPERIMENTATION

PSQL is a new kind of ORM for PHP 5.3. Based on a custom syntax that wraps around SQL code, it allows
to create "object oriented" sql. No PHP is involved in your models: you define them in SQL!

## Features

*  Easy to learn syntax
*  Your own sql queries
*  Custom filters to compute sql results
*  Possibility to compile all methods as sql stored procedures (if supported by database)
*  Complete PHP API
*  PHP stream wrapper to include models as php classes
*  Caching of compiled models

## Example

### Defining models

    User {
        
        // columns
        id int;
        email text;
        password text;
        firstName text;
        lastName text;
        
        // vars
        fullName { firstName || ' ' || lastName }
        SELECT { SELECT id, email, firstName, lastName, $fullName }
        
        static find_all() {
            $SELECT FROM users
        }

        static find_by_id($id) {
            $SELECT FROM users WHERE id = $id
        }
        
        @SingleValue
        static count() {
            SELECT COUNT(*) FROM users
        }
        
        find_messages() -> Message::find_all_by_user_id($id)
    }

    Message {

        id int;
        user_id int references users(id);
        message text;

        static find_all_by_user_id($id) {
            SELECT * FROM messages WHERE user_id = $id
        }
    }
    
### Usage

    $user = new User();
    $user->email = 'example@example.com';
    $user->password = md5('azerty');
    $user->save();

    $message = new Message();
    $message->user_id = $user->id;
    $message->message = 'hello world';
    $message->save();

    $user = User::find_by_id(1);
    echo $user->email;
    $messages = $user->find_messages();
    
## Models definition syntax

    [abstract|virtual] ModelName [as table_name] [extends ClassName] [implements Interface, ...] {

        column_name column_type additional_sql_declaration;
        
        var_name { var_value }
        
        [@Filter]
        [static] [private] function_name (params...) {
            sql_query
        }

    }
    
    func_name(params...) {
        sql_query
    }

