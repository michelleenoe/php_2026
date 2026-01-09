import mysql.connector
from mysql.connector import Error
import os

def update_reposts_table():
    try:
        # Connection parameters - these might need to be adjusted based on your setup
        connection = mysql.connector.connect(
            host='localhost',  # Adjust if needed - might need to use 'mariadb' if running inside docker network
            database='company',
            user='root',
            password='password'  # Using the password from docker-compose.yml
        )

        if connection.is_connected():
            cursor = connection.cursor()
            
            # Check if the old column exists before dropping it
            cursor.execute("SHOW COLUMNS FROM reposts LIKE 'repost_post_fk'")
            old_column_exists = cursor.fetchone()
            
            if old_column_exists:
                print("Dropping old column repost_post_fk...")
                cursor.execute("ALTER TABLE reposts DROP COLUMN repost_post_fk")
                print("Old column dropped successfully.")
            else:
                print("Column repost_post_fk does not exist, continuing...")
            
            # Check if new columns exist before adding them
            columns_to_add = []
            
            cursor.execute("SHOW COLUMNS FROM reposts LIKE 'repost_like_pk'")
            like_col_exists = cursor.fetchone()
            if not like_col_exists:
                columns_to_add.append("ADD COLUMN repost_like_pk CHAR(50) NULL")
                
            cursor.execute("SHOW COLUMNS FROM reposts LIKE 'repost_comment_pk'")
            comment_col_exists = cursor.fetchone()
            if not comment_col_exists:
                columns_to_add.append("ADD COLUMN repost_comment_pk CHAR(50) NULL")
                
            cursor.execute("SHOW COLUMNS FROM reposts LIKE 'repost_post_pk'")
            post_col_exists = cursor.fetchone()
            if not post_col_exists:
                columns_to_add.append("ADD COLUMN repost_post_pk CHAR(50) NULL")
            
            if columns_to_add:
                alter_query = "ALTER TABLE reposts " + ", ".join(columns_to_add)
                print("Adding new columns...")
                cursor.execute(alter_query)
                print("New columns added successfully.")
            else:
                print("All new columns already exist, skipping...")
            
            connection.commit()
            print("Database update completed successfully!")
            
    except Error as e:
        print(f"Error during database update: {e}")
        
    finally:
        try:
            if 'connection' in locals() and connection.is_connected():
                cursor.close()
                connection.close()
                print("MySQL connection closed.")
        except NameError:
            print("Connection was not established.")

if __name__ == "__main__":
    update_reposts_table()