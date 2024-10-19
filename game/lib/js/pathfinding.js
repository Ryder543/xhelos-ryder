/**
 * @author Joseka
 * Pathfinding Algorith from 
 * http://www.battleforcesonline.com/sharedFiles/pathFinding.php
 */

var action = "";	//action to perform when map is clicked
var startX = 0;		//starting position
var startY = 0;		//starting position
var moveDiagonal = "Y";	//unit can move diagonally

function getMapWidth() { return 15; }		//hardcoded
function getMapHeight() { return 7; }		//hardcoded
////////////////////////////////////////////////////////
//
// Clears any already shown path on the map
// Resets the start position
//
////////////////////////////////////////////////////////
function resetMap()
{
	for(i=0;i<getMapWidth();i++)
		for(k=0;k<getMapHeight();k++)
		{
			var d = document.getElementById("tile_" + i + "_" + k);
			d.innerHTML = parseInt(d.getAttribute("weight"));
			//d.innerHTML = i + "," + k + "<br>" + parseInt(d.getAttribute("weight"));
			if(parseInt(d.getAttribute("weight")) > 1)
			{
				d.style.background="gray";
			}
			else
			{
				d.style.background="green";
			}

		}

	var d = document.getElementById("tile_" + startX + "_" + startY);
	d.innerHTML = "S"
	d.style.background = "green";
}


////////////////////////////////////////////////////////
//
// Changes color/weight of a tile
//
////////////////////////////////////////////////////////
function changeColor(x,y,color)
{
	var d = document.getElementById("tile_" + x + "_" + y);
	d.style.background=color;
}


function setWeight(x, y, weight)
{
	var d = document.getElementById("tile_" + x + "_" + y);
	d.setAttribute("weight",weight);
	d.style.background="gray";
	d.innerHTML = weight;
}

////////////////////////////////////////////////////////
//
// Some array functions (inArray and Remove)
//
////////////////////////////////////////////////////////
Array.prototype.inArray = function (value) {
	var i;
	for (i=0; i < this.length; i++) {
		if (this[i] === value) {
			return true;
		}
	}
	return false;
};

Array.prototype.remove = function (value) {

	this.splice(this.indexOf(value));

	return true;

};

////////////////////////////////////////////////////////
//
// Takes a string point and converts to array
// IE: (2,5)
//		array[0] = 2
//		array[1] = 5
//
////////////////////////////////////////////////////////
function getPoint(str)
{
	var p = Array(2);
	var t = str.split("_");
	p[0] = parseInt(t[0]);
	p[1] = parseInt(t[1]);
	return p;
}

////////////////////////////////////////////////////////
//
// Gets whole number distance between two points
//
////////////////////////////////////////////////////////
function getDistance(x1, y1, x2, y2)
{
	return Math.floor((Math.abs(Math.sqrt(((x1 - x2) * (x1 - x2) ) + ( (y1 - y2) * (y1 - y2) ) ))));
}

////////////////////////////////////////////////////////
//
// Handles the tile clicked event
//
////////////////////////////////////////////////////////
function tileClicked(x,y)
{
	if(action == "setWeight")
	{
		setWeight(x, y, 50);
		//log.info("Movement Cost set to 50 at " + x + "," + y);
	}

	if(action == "setStart")
	{
		startX = x;
		startY = y;
		var d = document.getElementById("tile_" + x + "_" + y);
		d.setAttribute("weight",0);
		d.style.background="green";
		d.innerHTML = "S";
		//log.info("Starting at " + x + "," + y);
	}

	if(action == "stepThrough" || action == "")
	{
		findPath(startX,startY,x,y);
	}

	if(action == "findPath" || action == "")
	{
		findPath(startX,startY,x,y);
		quickPath();
	}
}


////////////////////////////////////////////////////////
//
// path finder
//
////////////////////////////////////////////////////////
var open = Array();
var finalPath = Array();
var closedNode = Array();
var pathCost = Array();
var pos = Array(2);
var iter = 0;
cost = 0;
leastCost = 999;
pathFound = "N";

function quickPath()
{
	while(pathFound == "N" && iter < 40)
	{
		nextStep();
	}
}

function findPath(x,y, finalX, finalY)
{
	resetMap();
	destX = finalX;
	destY = finalY;
	open = Array();
	finalPath = Array();
	closedNode = Array();
	pathCost = Array();
	pos = Array(2);
	iter = 0;
	cost = 0;
	leastCost = 999;
	pathFound = "N";

	open.push(x + "_" + y);
	pathCost.push(999);
	current = open[0];
	pathFound = "N";
	getNextBest = "N";
	nextStep();
}

function nextStep()
{
	//log.clear();

	if(current == destX + "_" + destY || iter > 100)
	{
		closedNode.push(destX + "_" + destY);
		finalPath.push(destX + "_" + destY);
		//log.info("Stopping Search");
	}
	else
	{
		closedNode.push(current);
		finalPath.push(current);
		pos = getPoint(current);
		n = getNeighbors(pos[0], pos[1]);
		var c = document.getElementById("tile_" + pos[0] + "_" + pos[1]);
		cost = cost + (parseInt(c.getAttribute("weight")));
		c.style.background="blue";
		c.innerHTML = cost;
		var neighbor = n.split("|");
		leastCost = cost + 20;

		//log.info(pos[0] + "," + pos[1] + " Cost:" + cost + " Dest:" + destX + "," + destY);
		//log.info("Neighbors: " + n);

		for(i = 0;i < neighbor.length;i++)
		{
			pos = getPoint(neighbor[i]);
			var next = document.getElementById("tile_" + pos[0] + "_" + pos[1]);

			if(next)
			{
				n_cost = parseInt(next.getAttribute("weight")) + cost + getDistance(pos[0],pos[1],destX, destY);
				next.style.background="#DDD";
				next.innerHTML = n_cost;

				if(n_cost < leastCost && pathFound == "N")
				{
					/*if(n_cost < pathCost[pathCost.length-1] && open.inArray(neighbor[i]))
					{
						pathCost.pop();
						//log.info("Popping" + open.pop() + " due to " + neighbor[i]);

						next.style.background="gray";

					}*/

					if(!open.inArray(neighbor[i]) && !closedNode.inArray(neighbor[i]))
					{
						leastCost = n_cost;
						open.push(neighbor[i]);
						pathCost.push(n_cost);
						next.style.background="yellow";
						next.innerHTML = n_cost;
						//log.info("Adding " + neighbor[i]);
						if(destX == pos[0] && destY == pos[1])
						{
							pathFound = "Y";
						}
					}


				}

			}



		}
	}

	iter++;

	if(current == open[open.length-1])
	{
		//we are stuck, get next best
		getNextBest = "Y";
		//log.info("Stuck at " + current);
	}


	current = open[open.length-1];

	for(i = 0;i < closedNode.length;i++)
	{
		pos = getPoint(closedNode[i]);
		var d = document.getElementById("tile_" + pos[0] + "_" + pos[1]);
		d.style.background="#FFF999";
		d.innerHTML = i;
	}

	//current = examinedArray[examined ];

	//log.info("Current:" + current);
	//log.info("Open:" + open);
	//log.info("Closed:" + closedNode);


}

////////////////////////////////////////////////////////
//
// Returns the neighbors that a move can be made to
// does not factor in weight
//
// This is a | delimited set, first token will be blank
// Example: |1,1|2,2|3,3
//
////////////////////////////////////////////////////////
function getNeighbors(x,y)
{
	var n = "";
	if(x + 1 < getMapWidth())
	{
		n+= "|" + (x+1) + "_" + y;
	}

	if(x - 1 >= 0)
	{
		n+= "|" + (x-1) + "_" + y ;
	}


	if(y + 1 < getMapHeight())
	{
		n+= "|" + x + "_" + (y+1) + "";
	}

	if(y - 1 >= 0)
	{
		n+= "|" + x + "_" + (y-1);
	}


	if(moveDiagonal == "Y")
	{
		if(y + 1 < getMapHeight()
			&& x +1 < getMapWidth())
		{
			n+= "|" + (x+1) + "_" + (y+1) + "";
		}


		if(y - 1 >=0
			&& x +1 < getMapWidth())
		{
			n+= "|" + (x+1) + "_" + (y-1) + "";
		}


		if(y + 1 < getMapHeight()
			&& x -1 >= 0)
		{
			n+= "|" + (x-1) + "_" + (y+1) + "";
		}

		if(y - 1 >= 0
			&& x -1 >= 0)
		{
			n+= "|" + (x-1) + "_" + (y-1) + "";
		}
	}

	return n;


}