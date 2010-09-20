# ClassQL

**HIGHLY EXPERIMENTAL**
THIS IS A WORK IN PROGRESS AND IN A STATE OF EXPERIMENTATION

ClassQL is a new kind of ORM for PHP 5.3. Based on a custom syntax that wraps around SQL code, it allows
to create "object oriented" SQL. No PHP is involved in your models: you define them in SQL!

## Features

*  Easy to learn syntax
*  Your own sql queries
*  Custom filters to compute sql results
*  Supports for eager loading
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
        fullName { firstName || ' ' || lastName }
        SELECT { SELECT id, email, firstName, lastName, $fullName }
        
        // by default, methods returns a collection of their defining class
        // the keyword $this will be replaced by the table name
        static find_all() {
            $SELECT FROM $this
        }

        // this methods returns a single User object
        static find_by_id($id) : User {
            $SELECT FROM $this WHERE id = $id
        }
        
        // returns a User object with a Message property containing a collection of Message objects
        static find_by_id_with_messages($id) : User + Message[] {
            $SELECT, m.message FROM $this JOIN messages m ON m.user_id = $this.id WHERE $this.id = $id
        }
        
        static count() : value {
            SELECT COUNT(*) FROM $this
        }
        
        // this method forwards the call to another one
        find_messages() -> Message::find_all_by_user_id($id)
    }

    Message {

        id int;
        user_id int references users(id);
        message text;

        static find_all_by_user_id($id) {
            SELECT * FROM $this WHERE user_id = $id
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
    
    $user = User::find_by_id_with_messages(1);
    echo $user->email;
    foreach ($user->Message as $message) {
        // ...
    }
    
## Models definition syntax

    [abstract|virtual] ModelName [as table_name] [extends ClassName] [implements Interface, ...] {

        column_name column_type additional_sql_declaration;
        
        var_name { var_value }
        
        [@Filter]
        [static] [private] function_name(params...) [ : returns_type ] {
            sql_query
        }
        
        [@Filter]
        [static] [private] function_name(params...) -> TargetModelName::function_name(args ...)
    }
    
    [@Filter]
    func_name(params...) [ : returns_type ] {
        sql_query
    }

