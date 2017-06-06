# MYDB

Comments in code

## MYDB::last_query
The last executed query.

## MYDB::query
Sends a generic query. Also saves the query string to `$this->last_query`.

## MYDB::insert
Creates and sends an 'INSERT INTO' query.

## MYDB::update
Creates and sends an 'UPDATE' query.

## MYDB::upsert
Create and sends an 'INSERT INTO' query. On duplicate key sends an 'UPDATE' query.

## MYDB::select
Create and sends a 'SELECT' query.

## MYDB::delete
Creates and sends a 'DELETE' query.
