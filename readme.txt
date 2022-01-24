README

Steps for installation and use.


1. Creating custom content type, Create a custom content type named 'resume' to store the data.
2. Add fields ( field_birth_date(Type Date), field_gender(Type Text), field_mobile(Type Number( Telephone Number), field_email_id(Type General( Email ), field_city( Type Text), field_country( Type Country))), )
3. Save the custom content
4. Install this module gaia_custom
5. Go to Postman of any other api calling app and make the request
6. Example to create the user call sitename.com/api/add_resume
{
    "user_name" : "Pemson Pereira",
    "user_dob" : "18/08/1991",
    "user_gender" : "male",
    "user_mobile" : "9049560250",
    "user_email" : "pemson18@gmail.com",
    "user_city": "Margao",
    "user_country" : "India",
}

7. To delete user call sitename.com/api/delete_resume
 pass the id of the resume i.e the node id

8. To fetch a single resume call sitename.com/api/fetch_resume
  Pass the id of the resume( nid )

9. To Edit a resume call sitename.com/api/edit_resume
  {
    "uid": "current_user_id"
    "nid": "some_nid"
    "user_name" : "Pemson Pereira Edited",
    "user_dob" : "18/08/1991",
    "user_gender" : "male",
    "user_mobile" : "9049560250",
    "user_email" : "pemson18@gmail.com",
    "user_city": "Margao",
    "user_country" : "India",
}