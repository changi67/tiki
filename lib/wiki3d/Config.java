package wiki3d;
import java.awt.*;
class Config
{
	//static Color background = new Color(111,200,211);
static Color background = new Color(255,255,255);
static Color facecolorblue=new Color(0,0,255);
static Color facecolorblack=new Color(0,0,0);
static Color colorface1=new Color(255,0,0);//color of the navigator
static Color colorface2=new Color(0,255,255);//-do- reverse side
static Color graphcolor=new Color(255,255,0);
static Color graphstringcolor =new Color(10,10,10);

static Color linkballcolor=new Color(255,0,0);//The link node color
static int  ballsize=30; //size of the nodes
static int randomlength=500;//random number to calculate length of the nodes 
static int  lengthmedian=250; //this is substracted from above to give symmetry
static int originx=200;//origin of the graph
static int originy=200;//
static int originz=0;//
static int faceoriginx=400,faceoriginy=400,camposz=500;//origin of navigator and z position of the viewer .
static int facesize=30;//size of the navigator

static int fieldOfView=200;//field of view
static int faceadjust=50;//to adjust the position of the navigator box
static int controlwidth=100;//width of the navigator box
static int windowwidth=600;//
static int gearing=1;//ratio movement of the navigator to the graph to slow down with increase
static int windowheight=500;
static int viewheight=500;//heght of view port including navigator
static int viewwidth=500;//width of view port
static int viewstartx=30;//upper start of the viewport
static int viewstarty=30;
static int xmax=800;//selfexplained
static int ymax=800;
static int zmax=1000;
static int xmin=-800;
static int ymin=-800;
static int zmin=-1000;
static float thetamax=20.0f;
static float thetamin=1.0f;
static float scalex = 3f;
static float scaley = 3f;


static int nodeDistance = 200;
public static int minimumBallSize = 2;
public static float maxSpeed = 50;
public static int navigationDepth = 2;
public static int throwDragTime = 500;








}