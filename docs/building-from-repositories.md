# Building From Repositories

You can customize the repository for an entity such as:

	Entity\User:
	  type: entity
	  table: users
	  id:
	    id:
	      type: integer
	      generator:
	        strategy: AUTO

	  oneToOne:
	    person:
	      targetEntity: Person
	      joinColumn:
	        name: person
	        referencedColumnName: id
	        nullable: false
	      cascade: [persist]

	  fields:
	    password:
	      type: string
	      nullable: false

	  lifecycleCallbacks: {  }
	  repositoryClass: Repository\UserRepository

You can extend `Tenet\EntityRepository` in your repository class in order to make use of it's more
concise `build()` method.

## Tenet\EntityRepository::build() Usage

The `build()` method returns an instance of Doctrine's `Paginator` class.  This allows you to use
the return as you would a normal iterator, but also to get the complete count of your query for
easy pagination.

### Build from UserRepository

Build the first page of user records, with 10 items where the associated person's first name is
Matthew:

	$repo  = $em->getRepository('Entity\User');
	$users = $repo->build([
		'person.firstName:=' => 'Matthew'
	], [], 10, 1);

Build all users which were created in the last week, order them by their date created:

	$repo  = $em->getRepository('Entity\User');
	$users = $repo->build([
		'dateCreated:>=' => new DateTime('-1 week')
	], [
		'dateCreated' => 'asc'
	]);

### Counting Results

You can call the `count()` method on the result of `build()` to get the non-limited count of
records.  That is, if you requested one page of 15 items, but there are 32 items, it would return
32 (ignoring the page and limit).

	$total_count = $users->count();

If you need the count of exactly how many records were returned respective of the limits, you
can count the iterator:

	$count = $users->getIterator()->count();