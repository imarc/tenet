# EntityRepository



### Extends

`Doctrine\ORM\EntityRepository`

#### Namespace

`Tenet`

#### Imports

<table>

	<tr>
		<th>Alias</th>
		<th>Namespace / Target</th>
	</tr>
	
	<tr>
		<td>Doctrine</td>
		<td>Doctrine</td>
	</tr>
	
	<tr>
		<td>InvalidArgumentException</td>
		<td>InvalidArgumentException</td>
	</tr>
	
	<tr>
		<td>EntityManager</td>
		<td>Doctrine\ORM\EntityManager</td>
	</tr>
	
	<tr>
		<td>ArrayCollection</td>
		<td>Doctrine\Common\Collections\ArrayCollection</td>
	</tr>
	
	<tr>
		<td>Paginator</td>
		<td>Doctrine\ORM\Tools\Pagination\Paginator</td>
	</tr>
	
	<tr>
		<td>Expr</td>
		<td>Doctrine\ORM\Query\Expr\Expr</td>
	</tr>
	
	<tr>
		<td>QueryBuilder</td>
		<td>Doctrine\ORM\QueryBuilder</td>
	</tr>
	
</table>

## Properties
### Static Properties
#### <span style="color:#6a6e3d;">$order</span>





## Methods

### Instance Methods
<hr />

#### <span style="color:#3e6a6e;">__construct()</span>


<hr />

#### <span style="color:#3e6a6e;">build()</span>


<hr />

#### <span style="color:#3e6a6e;">count()</span>


<hr />

#### <span style="color:#3e6a6e;">create()</span>


<hr />

#### <span style="color:#3e6a6e;">detach()</span>


<hr />

#### <span style="color:#3e6a6e;">findAll()</span>

Standard findAll with the option to add an orderBy

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$orderBy
			</td>
			<td>
									<a href="http://www.php.net/language.types.array.php">array</a>
				
			</td>
			<td>
				The order by clause to add
			</td>
		</tr>
			
	</tbody>
</table>


<hr />

#### <span style="color:#3e6a6e;">findBy()</span>

{@inheritDoc}


<hr />

#### <span style="color:#3e6a6e;">findOneBy()</span>

{@inheritDoc}


<hr />

#### <span style="color:#3e6a6e;">fetchAssociatedRepository()</span>

Fetch an associated repository by property name

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$entity_property
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The entity property pointing to the foreign repo
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			EntityRepository
		</dt>
		<dd>
			The related repository
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">store()</span>


<hr />

#### <span style="color:#3e6a6e;">expandBuildTerms()</span>

Expands build terms in `and` / `or` expressions

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$builder
			</td>
			<td>
									QueryBuilder				
			</td>
			<td>
				A builder to build the and/or expressions
			</td>
		</tr>
					
		<tr>
			<td>
				$terms
			</td>
			<td>
									<a href="http://www.php.net/language.types.array.php">array</a>
				
			</td>
			<td>
				The terms from which to expand comparisons
			</td>
		</tr>
					
		<tr>
			<td>
				&$pcount
			</td>
			<td>
									<a href="http://www.php.net/language.types.integer.php">integer</a>
				
			</td>
			<td>
				The current parameter count (when called recursively)
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Expr
		</dt>
		<dd>
			An `and` expression containing the terms and conditions passed by $terms
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">makeComparison()</span>

Makes a comparison based on shortened operators

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$builder
			</td>
			<td>
									QueryBuilder				
			</td>
			<td>
				A builder to build comparisons
			</td>
		</tr>
					
		<tr>
			<td>
				$field
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The field to make a comparison for
			</td>
		</tr>
					
		<tr>
			<td>
				$operator
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The comparison operation to use
			</td>
		</tr>
					
		<tr>
			<td>
				$value
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				The value for comparison
			</td>
		</tr>
					
		<tr>
			<td>
				$pcount
			</td>
			<td>
									<a href="http://www.php.net/language.types.integer.php">integer</a>
				
			</td>
			<td>
				The current parameter count
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Expr
		</dt>
		<dd>
			A mixed comparison expression of the equivalent $operator type
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">query()</span>




