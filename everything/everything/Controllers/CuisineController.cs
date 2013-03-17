using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace everything.Controllers
{
    //[Authorize]
    public class CuisineController : Controller
    {
        //
        // GET: /Cuisine/
        [HttpGet]
        //[Authorize(Roles = "Admin")]
        public ActionResult Search(string name = "default")
        {
            //throw new Exception("Oops!");
            //return Json(new {cuisineName = name}, JsonRequestBehavior.AllowGet);
            //return File(Server.MapPath("~/Content/Site.css"), "text/css");
            //return RedirectToRoute("Cuisine", new {name = "german"});
            //return RedirectToAction("Edit", "Photo", new {id = 54});
            name = Server.HtmlEncode(name);
            return Content(name);
        }

    }
}
