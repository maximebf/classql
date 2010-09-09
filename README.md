# NameToBeDetermined

NameToBeDetermined is a new kind of ORM for PHP 5.3. Based on a custom syntax that wraps around SQL code, it allows
to create "object oriented" sql. No PHP is involved in your models: you define them in SQL!

## Features

*  Easy to learn syntax
*  Your own sql queries
*  Custom filters to compute sql results
*  Compile all methods as sql stored procedures (if supported by database)
*  Relationships between models
*  Composite columns
*  Complete PHP API
*  PHP stream wrapper to include models as php classes
*  Caching of compiled models

## Example

### Defining models

    User (users) {
        
        id int
        email text
        password text
        firstName text
        lastName text
        fullName { firstName || ' ' || lastName }
        
        static find_all() {
            SELECT * FROM users
        }

        static find_by_id($id) {
            SELECT * FROM users WHERE id = $id
        }
        
        find_messages() -> Message::find_all_by_user_id($id)
    }

    Message (messages) {

        id int
        user_id int
        message text
        
        foreign key messages_user_id_users_id on user_id references users(id)

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

[@Attribute]
ModelName {

    field_declaration
    constraint_declaration
    
    [@Attribute]
    [static] function_name (params...) {
        sql_query
    }

}

